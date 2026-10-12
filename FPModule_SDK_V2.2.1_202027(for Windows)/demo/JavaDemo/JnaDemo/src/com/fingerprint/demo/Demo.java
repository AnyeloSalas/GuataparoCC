package com.fingerprint.demo;


import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.util.Properties;

import com.bio.fingerprint.FPJna;
import com.bio.fingerprint.FPMsg;
import com.bio.fingerprint.FpCallback;
import com.sun.jna.Pointer;
import com.sun.jna.ptr.IntByReference;


public class Demo {

	static FPJna mFPJna=new FPJna();;
	static int index =0;
	static FpCallback mcallback = new FpCallback() {
		
		
		@Override
		public void FpMessageHandler(int enMsgType, FPMsg pMsgData) {
			switch(enMsgType)
			{
				case FPJna.FP_MSG_PRESS_FINGER:
					System.out.println(">>>>Place your finger");
					break;
				case FPJna.FP_MSG_RISE_FINGER:
					System.out.println(">>>>Lift your finger");
					break;
				case FPJna.FP_MSG_ENROLL_TIME:
					index=pMsgData.dwArg1;
					System.out.println(">>>>Enroll time:"+index);
					break;
				case FPJna.FP_MSG_CAPTURED_IMAGE:
					int w=pMsgData.dwArg1;
					int h=pMsgData.dwArg2;
					Pointer p = pMsgData.pbyImage;
				    byte[]data =p.getByteArray(0, w*h) ; 
				    SaveBmp("Enroll"+index+".bmp",data,w,h);
					System.out.println(">>>>enMsgType:w:"+w+"h:"+h);
					break;
			}
		}

		
	};
	public static void SaveBmp(String path,byte[] data,int w,int h) {
		FileOutputStream fos;
		byte[]d = mFPJna.RawToBmpData(data,w,h);
		try {
			fos=new FileOutputStream(path);
			fos.write(d);
		    fos.flush();
		    fos.close();
		} catch (FileNotFoundException e) {
			e.printStackTrace();
		}
		catch (IOException e) {
			e.printStackTrace();
		}
	}
	public static void main(String[] args) {
		
		
		int iRet=FPJna.FP_SUCCESS;

		//Init
		System.out.println("LoadLibrary");
		mFPJna.LoadLibrary();

		//OpenDevice
		System.out.println("start OpenDevice");
		iRet = mFPJna.OpenDevice();
		System.out.println("finish OpenDevice:"+iRet);
		if(iRet!=FPJna.FP_SUCCESS)
		{
			System.out.println("Exit");
			return;
		}
		
		//InstallMessageHandler
		System.out.println("start InstallMessageHandler");
		iRet = mFPJna.InstallMessageHandler(mcallback);
		System.out.println("finish InstallMessageHandler:"+iRet);
		if(iRet!=FPJna.FP_SUCCESS)
		{
			System.out.println("Exit");
			mFPJna.CloseDevice();
			return;
		}
		//GetDeviceInfo
		String devInfo = mFPJna.GetDeviceInfo();
		System.out.println("GetDeviceInfo:"+devInfo);
		if(devInfo==null)
		{
			System.out.println("Exit");
			mFPJna.CloseDevice();
			return;
		}	
		
		//GetSDKVersion
		String sdkInfo = mFPJna.GetSDKVersion();
		System.out.println("GetSDKVersion:"+sdkInfo);
		if(sdkInfo==null)
		{
			System.out.println("Exit");
			mFPJna.CloseDevice();
			return;
		}
		
		//DetectFinger
		long st = System.currentTimeMillis();
		IntByReference pdwFpstatus = new IntByReference();
		IntByReference pdwWidth = new IntByReference();
		IntByReference pdwHeight = new IntByReference();
		byte[]pbyImageData = new byte[FPJna.FP_IMAGE_WIDTH*FPJna.FP_IMAGE_HEIGHT];
		System.out.println("start DetectFinger");
		System.out.println(">>>>Place your finger in 15s");
		while((System.currentTimeMillis()-st)/1000<15)
		{
			iRet=mFPJna.DetectFinger(pdwFpstatus);
			if(iRet!=FPJna.FP_SUCCESS)
			{
				System.out.println("Exit");
				mFPJna.CloseDevice();
				return;
			}
			if(pdwFpstatus.getValue()==1)
			{
				System.out.println(">>>>CaptureImage...");
				iRet =mFPJna.CaptureImage(pbyImageData, pdwWidth, pdwHeight);
				if(iRet!=FPJna.FP_SUCCESS)
				{
					System.out.println("Exit");
					mFPJna.CloseDevice();
					return;
				}
				SaveBmp("CaptureImage.bmp",pbyImageData,pdwWidth.getValue(),pdwHeight.getValue());
				System.out.println(">>>>CaptureImage:"+iRet);
				break;
			}
		}
		if(pdwFpstatus.getValue()!=1)
			System.out.println("finish DetectFinger TimeOut");
		else
			System.out.println("finish DetectFinger");
		
		//SetTimeout 只对FpEnroll有效
		System.out.println("start SetTimeout");
		iRet=mFPJna.SetTimeout(15);
		System.out.println("finish SetTimeout:"+iRet);
		if(iRet!=FPJna.FP_SUCCESS)
		{
			System.out.println("Exit");
			mFPJna.CloseDevice();
			return;
		}
		
		//GetTimeout
		System.out.println("start GetTimeout");
		IntByReference ref = new IntByReference();
		iRet=mFPJna.GetTimeout(ref);
		System.out.println("finish GetTimeout:"+iRet+"Time:"+ref.getValue());
		if(iRet!=FPJna.FP_SUCCESS)
		{
			System.out.println("Exit");
			mFPJna.CloseDevice();
			return;
		}
		//FpEnroll
		byte[] pbyFpTemplate=new byte[FPJna.FP_FEATURE_LEN];
		System.out.println("start FpEnroll");
		mFPJna.SetCollectTimes(3);
		iRet =mFPJna.FpEnroll(pbyFpTemplate);
		System.out.println("finish FpEnroll:"+iRet);
		if(iRet!=FPJna.FP_SUCCESS)
		{
			System.out.println("Exit");
			mFPJna.CloseDevice();
			return;
		}
		//GetQuality
		System.out.println("start GetQuality");
		iRet =mFPJna.GetQuality(pbyFpTemplate);
		System.out.println("finish GetQuality:"+iRet);
		
		
		//MatchTemplate
		byte[] pbyFpFeature=new byte[FPJna.FP_FEATURE_LEN];
		System.out.println("start collect finger");
		mFPJna.SetCollectTimes(1);
		iRet =mFPJna.FpEnroll(pbyFpFeature);
		System.out.println("finish collect finger:"+iRet);
		if(iRet!=FPJna.FP_SUCCESS)
		{
			System.out.println("Exit");
			mFPJna.CloseDevice();
			return;
		}
		System.out.println("start MatchTemplate");
		iRet =mFPJna.MatchTemplate(pbyFpFeature,pbyFpTemplate,3);
		System.out.println("finish MatchTemplate:"+iRet);
		if(iRet!=FPJna.FP_SUCCESS)
		{
			System.out.println("Exit");
			mFPJna.CloseDevice();
			return;
		}		
		System.out.println("MatchTemplate Success");		
		//CloseDevice
		mFPJna.CloseDevice();
		System.out.println("Exit");
	}

}
