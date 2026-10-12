import ctypes
import os
import sys
import base64
import json
import argparse

# Configuración de ruta de la DLL del SDK Hikvision / FPModule
# Este bridge es compatible con el lector Hikvision DS-K1F820-F y otros basados en FPModule.
DLL_PATH = r"C:\laragon\www\php_system\huella\FPModule_SDK_V2.2.1_202027(for Windows)\libs\x64\FPModule_SDK_x64.dll"

def load_sdk():
    try:
        if not os.path.exists(DLL_PATH):
            return None, f"DLL no encontrada en: {DLL_PATH}"
        return ctypes.WinDLL(DLL_PATH), None
    except Exception as e:
        return None, f"Error al cargar la DLL: {str(e)}"

def setup_sdk(fp_sdk):
    # Prototipos
    fp_sdk.FPModule_OpenDevice.restype = ctypes.c_int
    fp_sdk.FPModule_CloseDevice.restype = ctypes.c_int
    fp_sdk.FPModule_SetTimeout.argtypes = [ctypes.c_int]
    fp_sdk.FPModule_SetTimeout.restype = ctypes.c_int
    fp_sdk.FPModule_SetCollectTimes.argtypes = [ctypes.c_int]
    fp_sdk.FPModule_SetCollectTimes.restype = ctypes.c_int
    fp_sdk.FPModule_FpEnroll.argtypes = [ctypes.POINTER(ctypes.c_ubyte)]
    fp_sdk.FPModule_FpEnroll.restype = ctypes.c_int
    fp_sdk.FPModule_MatchTemplate.argtypes = [ctypes.POINTER(ctypes.c_ubyte), ctypes.POINTER(ctypes.c_ubyte), ctypes.c_int]
    fp_sdk.FPModule_MatchTemplate.restype = ctypes.c_int

    # Message Handler
    CMPFUNC = ctypes.WINFUNCTYPE(None, ctypes.c_int, ctypes.c_void_p)
    fp_sdk.FPModule_InstallMessageHandler.argtypes = [CMPFUNC]
    fp_sdk.FPModule_InstallMessageHandler.restype = ctypes.c_int
    return CMPFUNC

def enroll(fp_sdk, cmp_func, timeout=30, times=0):
    try:
        if fp_sdk.FPModule_OpenDevice() != 0:
            return {"status": "error", "message": "Dispositivo no encontrado"}

        # Handler de mensajes (opcional, para feedback en stderr)
        def py_handler(msg_type, msg_data):
            msgs = {0: "Coloque dedo", 1: "Levante dedo", 2: "Procesando", 3: "Capturada"}
            sys.stderr.write(f">>> {msgs.get(msg_type, '...')}\n")
            sys.stderr.flush()
        
        c_handler = cmp_func(py_handler)
        fp_sdk.FPModule_InstallMessageHandler(c_handler)
        fp_sdk.FPModule_SetTimeout(timeout)
        fp_sdk.FPModule_SetCollectTimes(times)

        template_buffer = (ctypes.c_ubyte * 512)()
        res = fp_sdk.FPModule_FpEnroll(template_buffer)
        
        if res == 0:
            template_b64 = base64.b64encode(bytes(template_buffer)).decode('utf-8')
            output = {"status": "success", "template": template_b64, "quality": 100}
        elif res == 2:
            output = {"status": "timeout", "message": "Tiempo agotado"}
        else:
            output = {"status": "error", "message": f"Error SDK: {res}"}
            
        fp_sdk.FPModule_CloseDevice()
        return output
    except Exception as e:
        return {"status": "error", "message": str(e)}

def verify(fp_sdk, cmp_func, target_template_b64, timeout=30):
    try:
        if fp_sdk.FPModule_OpenDevice() != 0:
            return {"status": "error", "message": "Dispositivo no encontrado"}

        # Primero capturamos una huella viva
        template_buffer = (ctypes.c_ubyte * 512)()
        fp_sdk.FPModule_SetTimeout(timeout)
        fp_sdk.FPModule_SetCollectTimes(1) # 1 vez para verificación rápida
        
        res_enroll = fp_sdk.FPModule_FpEnroll(template_buffer)
        if res_enroll != 0:
            fp_sdk.FPModule_CloseDevice()
            return {"status": "fail", "message": "No se pudo capturar huella para verificar"}

        # Decodificar el template objetivo
        target_bytes = base64.b64decode(target_template_b64)
        target_buffer = (ctypes.c_ubyte * 512).from_buffer_copy(target_bytes)

        # Comparar (Nivel de seguridad 3 por defecto)
        res_match = fp_sdk.FPModule_MatchTemplate(template_buffer, target_buffer, 3)
        
        if res_match == 0:
            output = {"status": "success", "match": True}
        else:
            output = {"status": "success", "match": False}

        fp_sdk.FPModule_CloseDevice()
        return output
    except Exception as e:
        return {"status": "error", "message": str(e)}

def match(fp_sdk, t1_b64, t2_b64):
    try:
        # Decodificar templates
        t1_bytes = base64.b64decode(t1_b64)
        t2_bytes = base64.b64decode(t2_b64)
        
        t1_buffer = (ctypes.c_ubyte * 512).from_buffer_copy(t1_bytes)
        t2_buffer = (ctypes.c_ubyte * 512).from_buffer_copy(t2_bytes)

        # Comparar (Nivel de seguridad 3 por defecto)
        res_match = fp_sdk.FPModule_MatchTemplate(t1_buffer, t2_buffer, 3)
        
        if res_match == 0:
            return {"status": "success", "match": True, "score": 100}
        else:
            return {"status": "success", "match": False, "score": 0}
    except Exception as e:
        return {"status": "error", "message": f"Error en match: {str(e)}"}

def batch_match(fp_sdk, live_b64, templates_b64_list):
    try:
        live_bytes = base64.b64decode(live_b64)
        live_buffer = (ctypes.c_ubyte * 512).from_buffer_copy(live_bytes)
        
        best_score = 0
        best_idx = None
        
        for idx, t2_b64 in enumerate(templates_b64_list):
            try:
                t2_bytes = base64.b64decode(t2_b64)
                t2_buffer = (ctypes.c_ubyte * 512).from_buffer_copy(t2_bytes)
                res_match = fp_sdk.FPModule_MatchTemplate(live_buffer, t2_buffer, 3)
                if res_match == 0:
                    return {"status": "success", "match": True, "score": 100, "idx": idx}
            except Exception as e:
                pass
                
        return {"status": "success", "match": False, "score": 0}
    except Exception as e:
        return {"status": "error", "message": f"Error en batch_match: {str(e)}"}

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Puente de Huella Python para SDK V2.2.1")
    parser.add_argument("mode", choices=["enroll", "verify", "match", "batch_match"], help="Modo de operación")
    parser.add_argument("--template", help="Template en Base64 para modo verify/match")
    parser.add_argument("--template2", help="Segundo template en Base64 para modo match")
    parser.add_argument("--timeout", type=int, default=30, help="Timeout en segundos")
    parser.add_argument("--times", type=int, default=0, help="Cantidad de capturas (0 para enrolamiento, 1 para verificación)")
    
    args = parser.parse_args()
    
    fp_sdk, err = load_sdk()
    if err:
        print(json.dumps({"status": "error", "message": err}))
        sys.exit(1)
        
    cmp_func = setup_sdk(fp_sdk)
    
    if args.mode == "enroll":
        print(json.dumps(enroll(fp_sdk, cmp_func, args.timeout, args.times)))
    elif args.mode == "verify":
        if not args.template:
            print(json.dumps({"status": "error", "message": "Se requiere --template para modo verify"}))
        else:
            print(json.dumps(verify(fp_sdk, cmp_func, args.template, args.timeout)))
    elif args.mode == "match":
        if not args.template or not args.template2:
            print(json.dumps({"status": "error", "message": "Se requieren --template y --template2 para modo match"}))
        else:
            print(json.dumps(match(fp_sdk, args.template, args.template2)))
    elif args.mode == "batch_match":
        # Read JSON from stdin because command line arguments can be too long for many templates
        input_data = sys.stdin.read()
        try:
            data = json.loads(input_data)
            live_temp = data.get('template')
            templates_list = data.get('templates', [])
            if not live_temp or not templates_list:
                print(json.dumps({"status": "error", "message": "Se requiere template y listado de templates"}))
            else:
                print(json.dumps(batch_match(fp_sdk, live_temp, templates_list)))
        except Exception as e:
            print(json.dumps({"status": "error", "message": f"Invalid JSON input: {str(e)}"}))
