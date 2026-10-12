import ctypes
import os
import time
import sqlite3

# --- Constantes y Configuración ---
DB_FILE = "fingerprints.db"
SDK_DLL_PATH = os.path.join(
    r"C:\laragon\www\huella\FPModule_SDK_V2.2.1_202027(for Windows)",
    "bin",
    "x64",
    "FPModule_SDK_x64.dll"
)

# --- Carga de la DLL del SDK ---
try:
    g_sdk_dll = ctypes.CDLL(SDK_DLL_PATH)
    print(f"DLL cargada exitosamente desde: {SDK_DLL_PATH}")
except OSError as e:
    print(f"Error al cargar la DLL desde {SDK_DLL_PATH}: {e}")
    g_sdk_dll = None

if g_sdk_dll:
    # --- Definiciones de tipos y funciones del SDK (ctypes) ---
    INT = ctypes.c_int
    VOID_P = ctypes.c_void_p
    LPSTR = ctypes.POINTER(ctypes.c_char)
    LPUCHAR = ctypes.POINTER(ctypes.c_ubyte)
    TEMPLATE_SIZE = 512
    FP_MSG_PRESS_FINGER, FP_MSG_RISE_FINGER, FP_MSG_ENROLL_TIME, FP_MSG_CAPTURED_IMAGE = 0, 1, 2, 3
    FpMessageHandler = ctypes.WINFUNCTYPE(None, INT, VOID_P)

    def define_sdk_functions():
        g_sdk_dll.FPModule_OpenDevice.argtypes = []
        g_sdk_dll.FPModule_OpenDevice.restype = INT
        g_sdk_dll.FPModule_CloseDevice.argtypes = []
        g_sdk_dll.FPModule_CloseDevice.restype = INT
        g_sdk_dll.FPModule_SetTimeout.argtypes = [INT]
        g_sdk_dll.FPModule_SetTimeout.restype = INT
        g_sdk_dll.FPModule_InstallMessageHandler.argtypes = [FpMessageHandler]
        g_sdk_dll.FPModule_InstallMessageHandler.restype = INT
        g_sdk_dll.FPModule_FpEnroll.argtypes = [LPUCHAR]
        g_sdk_dll.FPModule_FpEnroll.restype = INT
        g_sdk_dll.FPModule_MatchTemplate.argtypes = [LPUCHAR, LPUCHAR, INT]
        g_sdk_dll.FPModule_MatchTemplate.restype = INT
    define_sdk_functions()

    # --- Funciones de Base de Datos (SQLite) ---
    def init_db():
        """Crea la tabla de huellas si no existe."""
        with sqlite3.connect(DB_FILE) as conn:
            cursor = conn.cursor()
            cursor.execute("""
                CREATE TABLE IF NOT EXISTS fingerprints (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_name TEXT NOT NULL UNIQUE,
                    template_data BLOB NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            """)
            print("Base de datos inicializada correctamente.")

    def save_fingerprint_to_db(user_name, template_data):
        """Guarda una nueva huella en la base de datos."""
        try:
            with sqlite3.connect(DB_FILE) as conn:
                cursor = conn.cursor()
                cursor.execute("INSERT INTO fingerprints (user_name, template_data) VALUES (?, ?)", (user_name, template_data))
                print(f"Huella para el usuario '{user_name}' guardada en la base de datos.")
                return True
        except sqlite3.IntegrityError:
            print(f"Error: El nombre de usuario '{user_name}' ya existe.")
            return False

    def get_all_fingerprints_from_db():
        """Obtiene todas las huellas de la base de datos."""
        with sqlite3.connect(DB_FILE) as conn:
            cursor = conn.cursor()
            cursor.execute("SELECT user_name, template_data FROM fingerprints")
            return cursor.fetchall()

    # --- Lógica del SDK ---
    def _py_message_handler(msg_type, msg_data):
        if msg_type == FP_MSG_PRESS_FINGER: print(">>> [SDK] Por favor, presione el dedo sobre el lector.")
        elif msg_type == FP_MSG_RISE_FINGER: print(">>> [SDK] Por favor, retire el dedo.")
        elif msg_type == FP_MSG_ENROLL_TIME: print(f">>> [SDK] Inscripción en progreso...")
        elif msg_type == FP_MSG_CAPTURED_IMAGE: print(">>> [SDK] Imagen de huella capturada.")
    c_message_handler = FpMessageHandler(_py_message_handler)

    def open_device():
        if g_sdk_dll.FPModule_OpenDevice() != 0: return False
        g_sdk_dll.FPModule_InstallMessageHandler(c_message_handler)
        g_sdk_dll.FPModule_SetTimeout(20)
        return True

    def close_device():
        g_sdk_dll.FPModule_CloseDevice()

    def enroll_fingerprint():
        print("\n--- Iniciando proceso de captura de huella ---")
        template_buffer = (ctypes.c_ubyte * TEMPLATE_SIZE)()
        ret = g_sdk_dll.FPModule_FpEnroll(template_buffer)
        if ret == 0:
            print("--- Captura de huella finalizada con éxito ---")
            return bytes(template_buffer)
        else:
            print(f"--- Fallo en la captura. Código de error: {ret} (3=Fallo de enrolamiento, 2=Timeout) ---")
            return None

    def match_fingerprints(template1, template2):
        if not template1 or not template2: return False
        buffer1 = (ctypes.c_ubyte * TEMPLATE_SIZE)(*template1)
        buffer2 = (ctypes.c_ubyte * TEMPLATE_SIZE)(*template2)
        return g_sdk_dll.FPModule_MatchTemplate(buffer1, buffer2, 3) == 0

    # --- Lógica Principal de la Aplicación ---
    def main():
        if not g_sdk_dll:
            print("No se pudo iniciar la aplicación porque la DLL no fue cargada.")
            return

        init_db()

        while True:
            print("\n===== MENÚ PRINCIPAL =====")
            print("1. Registrar nueva huella")
            print("2. Verificar huella (Identificar)")
            print("3. Salir")
            choice = input("Seleccione una opción: ")

            if choice == '1':
                user_name = input("Ingrese el nombre de usuario para la nueva huella: ")
                if not user_name:
                    print("El nombre de usuario no puede estar vacío.")
                    continue
                
                print("Abriendo dispositivo para registrar...")
                if open_device():
                    new_template = enroll_fingerprint()
                    if new_template:
                        save_fingerprint_to_db(user_name, new_template)
                    close_device()
                    print("Dispositivo cerrado.")
                else:
                    print("Error: No se pudo abrir el dispositivo.")

            elif choice == '2':
                print("Abriendo dispositivo para verificar...")
                if open_device():
                    live_template = enroll_fingerprint()
                    if live_template:
                        all_fingerprints = get_all_fingerprints_from_db()
                        if not all_fingerprints:
                            print("No hay huellas registradas en la base de datos.")
                        else:
                            found_match = False
                            for user_name, stored_template in all_fingerprints:
                                if match_fingerprints(live_template, stored_template):
                                    print(f"\n======================================")
                                    print(f"  ACCESO CONCEDIDO: ¡Bienvenido, {user_name}!")
                                    print(f"======================================")
                                    found_match = True
                                    break
                            
                            if not found_match:
                                print("\n**************************************")
                                print(f"  ACCESO DENEGADO: Huella no reconocida.")
                                print(f"**************************************")
                    close_device()
                    print("Dispositivo cerrado.")
                else:
                    print("Error: No se pudo abrir el dispositivo.")

            elif choice == '3':
                print("Saliendo de la aplicación.")
                break
            else:
                print("Opción no válida. Por favor, intente de nuevo.")

    if __name__ == "__main__":
        main()
