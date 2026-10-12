using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Windows.Forms;
using System.Text.RegularExpressions;
using System.Threading;
using System.IO;
using System.Runtime.InteropServices;

namespace Demo
{
    public partial class Demo : Form
    {
        int iRet = FPutils.FP_SUCCESS;
        int index = 0;
        byte[] image = new byte[FPutils.FP_IMAGE_WIDTH * FPutils.FP_IMAGE_HEIGHT];
        byte[] bmp = new byte[FPutils.FP_IMAGE_WIDTH * FPutils.FP_IMAGE_HEIGHT + FPutils.FP_BMP_HEADER];
        byte[] tempArray = new byte[FPutils.FP_FTP_MAX*1000];
        int nTemp = 0;
        static FPMsg.FpMessageHandler FpMessageHandler = null;//防止Invoke异常
        public Demo()
        {
            InitializeComponent();
            //注册回调
            FpMessageHandler = new FPMsg.FpMessageHandler(FpMessageCallback);
            FPutils.FPModule_InstallMessageHandler(FpMessageHandler);
            GC.KeepAlive(FpMessageHandler);
        }

        private void FPModule_OpenDevice_Click(object sender, EventArgs e)
        {
            iRet = FPutils.FPModule_OpenDevice();
            if (iRet == FPutils.FP_SUCCESS)
            {
                MsgBox.Text = "OpenDevice OK";
            }
            else 
            {
                MessageBox.Show("OpenDevice failed");
            }
        }

        private void FPModule_GetSDKVersion_Click(object sender, EventArgs e)
        {
            byte[] info = new byte[64];
            iRet = FPutils.FPModule_GetSDKVersion(info);
            if (iRet == FPutils.FP_SUCCESS)
            {
                MsgBox.Text = Encoding.Default.GetString(info);
            }
            else
            {
                MessageBox.Show("GetSDKVersion failed");
            }
   
        }

        private void FPModule_GetDeviceInfo_Click(object sender, EventArgs e)
        {
            byte[] info = new byte[64];
            iRet = FPutils.FPModule_GetDeviceInfo(info);
            if (iRet == FPutils.FP_SUCCESS)
            {
                 MsgBox.Text = Encoding.Default.GetString(info);
            }
            else
            {
                MessageBox.Show("GetDeviceInfo failed");
            }
        }

        private void FPModule_SetTimeout_Click(object sender, EventArgs e)
        {
            iRet = FPutils.FPModule_SetTimeout(15);
            if (iRet == FPutils.FP_SUCCESS)
            {
                MsgBox.Text = "SetTimeout success:" + 15+"s";
            }
            else
            {
                MessageBox.Show("SetTimeout failed");
            }
        }

        private void FPModule_DetectFinger_Click(object sender, EventArgs e)
        {
            int pdwFpstatus = 0;
            iRet = FPutils.FPModule_DetectFinger(ref pdwFpstatus);
            if (iRet == FPutils.FP_SUCCESS)
            {
               MsgBox.Text ="DetectFinger success:" + pdwFpstatus;
            }
            else
            {
                MessageBox.Show("DetectFinger failed");
            }
        }

        private void FPModule_CaptureImage_Click(object sender, EventArgs e)
        {

            int w=0, h=0;
            iRet = FPutils.FPModule_CaptureImage(image,ref w,ref h);
            if (iRet == FPutils.FP_SUCCESS)
            {
                // 写图像到文件
                // 将二进制拓印图像转换为BMP格式   
                FPutils.ImgBufferToBmpBuffer(image, w, h, bmp);

                // 写入文件
                System.IO.MemoryStream ms = new MemoryStream(bmp, true);
                ms.Position = 0;
                Image _Img = Image.FromStream(ms);
                _Img.Save(Application.StartupPath + "//" + "CaptureImage.bmp");

                // 在界面显示
                fpImageBox.Image = _Img;
            }
            else
            {
                MessageBox.Show("CaptureImage failed");
            }
        }
        private void SaveTemp(string path, byte[] szTmp)
        {
            FileStream file = new FileStream(path, FileMode.Create);
            //将byte数组写入文件中
            file.Write(szTmp, 0, szTmp.Length);
           //所有流类型都要关闭流，否则会出现内存泄露问题
            file.Close();
        }
        private void FPModule_FpEnroll_Click(object sender, EventArgs e)
        {
            byte[] data= new byte[FPutils.FP_FTP_MAX];

            FPutils.FPModule_SetCollectTimes(3);
            iRet = FPutils.FPModule_FpEnroll(data);
            if (iRet == FPutils.FP_SUCCESS)
            {

                Buffer.BlockCopy(data, 0, tempArray, nTemp * FPutils.FP_FTP_MAX, FPutils.FP_FTP_MAX);
                MsgBox.Text = "FpEnroll success ,Score" + FPutils.FPModule_GetQuality(data) + "ID:" + nTemp + "Cnt:" + (nTemp+1);
                SaveTemp("temp.dat", data);
                nTemp++;
            }
            else
            {
                MessageBox.Show("FpEnroll failed");
            }
            fpImageBox.Image = global::Demo.Properties.Resources.backup; ;
        }

        private void FPModule_CloseDevice_Click(object sender, EventArgs e)
        {
            iRet = FPutils.FPModule_CloseDevice();
            if (iRet == FPutils.FP_SUCCESS)
            {
                MsgBox.Text ="CloseDevice success";
            }
            else
            {
                MessageBox.Show("CloseDevice failed");
            }
        }

        private void FPModule_GetTimeout_Click(object sender, EventArgs e)
        {
            int iTime = 0;
            iRet = FPutils.FPModule_GetTimeout(ref iTime);
            if (iRet == FPutils.FP_SUCCESS)
            {
                MsgBox.Text ="GetTimeout success:" + iTime+"s";
            }
            else
            {
                MessageBox.Show("GetTimeout failed");
            }
        }
        void FpMessageCallback(FPMsg.FP_MSG_TYPE_T enMsgType, IntPtr pMsgData)
        {

            if (enMsgType == FPMsg.FP_MSG_TYPE_T.FP_MSG_RISE_FINGER)
            {
                MsgBox.Text = "Lift and rest your finger";
            }
            else if (enMsgType == FPMsg.FP_MSG_TYPE_T.FP_MSG_PRESS_FINGER)
            {
                MsgBox.Text = "Place Your Finger";
            }
            else if (enMsgType == FPMsg.FP_MSG_TYPE_T.FP_MSG_ENROLL_TIME)
            {
                int []time = new int[1];
                Marshal.Copy(pMsgData, time, 0,1);
                MsgBox.Text = "Enroll Time" + time[0];
                index = time[0];
            }
            else if (enMsgType == FPMsg.FP_MSG_TYPE_T.FP_MSG_CAPTURED_IMAGE)
            {
                object obj = Marshal.PtrToStructure(pMsgData, typeof(FPutils.FP_IMAGE_DATA));
                var pInfos = (FPutils.FP_IMAGE_DATA)obj;

                Marshal.Copy(pInfos.pbyImage, image, 0, pInfos.dwWidth * pInfos.dwHeight);
                FPutils.ImgBufferToBmpBuffer(image, pInfos.dwWidth, pInfos.dwHeight, bmp);

                // 写入文件
                System.IO.MemoryStream ms = new MemoryStream(bmp, true);
                ms.Position = 0;
                Image _Img = Image.FromStream(ms);
                _Img.Save(Application.StartupPath + "//" + "Enroll" + index + ".bmp");

                // 在界面显示
                fpImageBox.Image = _Img;
            }
            Application.DoEvents();
        }

        private void FPModule_MatchTemplate_Click(object sender, EventArgs e)
        {
            byte[] data = new byte[FPutils.FP_FTP_MAX];
            byte[] temp = new byte[FPutils.FP_FTP_MAX];

            FPutils.FPModule_SetCollectTimes(1);
            iRet = FPutils.FPModule_FpEnroll(data);
            if (iRet == FPutils.FP_SUCCESS)
            {

                for (int i = 0; i < nTemp;i++)
                {
                    Buffer.BlockCopy(tempArray, i * FPutils.FP_FTP_MAX,temp,0,FPutils.FP_FTP_MAX);
                    if (FPutils.FPModule_MatchTemplate(data, temp, 3) == FPutils.FP_SUCCESS)
                    {
                        MsgBox.Text = "MatchTemplate success ,ID:"+i;
                        fpImageBox.Image = global::Demo.Properties.Resources.backup; ;
                        return;
                    }
                }
                MsgBox.Text = "MatchTemplate failed";
                fpImageBox.Image = global::Demo.Properties.Resources.backup; ;
                return;   
                
            }
            else
            {
                MessageBox.Show("MatchTemplate failed");
            }
            fpImageBox.Image = global::Demo.Properties.Resources.backup; ;
        }
    }
}
