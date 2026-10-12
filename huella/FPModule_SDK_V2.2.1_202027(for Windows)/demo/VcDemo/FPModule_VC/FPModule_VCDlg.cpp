
// FPModule_VCDlg.cpp : 实现文件
//

#include "stdafx.h"
#include "FPModule_VC.h"
#include "FPModule_VCDlg.h"
#include "afxdialogex.h"
#include <atlimage.h>


#ifdef _DEBUG
#define new DEBUG_NEW
#endif


// 用于应用程序“关于”菜单项的 CAboutDlg 对话框

class CAboutDlg : public CDialogEx
{
public:
	CAboutDlg();

// 对话框数据
	enum { IDD = IDD_ABOUTBOX };

	protected:
	virtual void DoDataExchange(CDataExchange* pDX);    // DDX/DDV 支持

// 实现
protected:
	DECLARE_MESSAGE_MAP()
};

CAboutDlg::CAboutDlg() : CDialogEx(CAboutDlg::IDD)
{
}

void CAboutDlg::DoDataExchange(CDataExchange* pDX)
{
	CDialogEx::DoDataExchange(pDX);
}

BEGIN_MESSAGE_MAP(CAboutDlg, CDialogEx)
END_MESSAGE_MAP()


// CFPModule_VCDlg 对话框



CFPModule_VCDlg::CFPModule_VCDlg(CWnd* pParent /*=NULL*/)
	: CDialogEx(CFPModule_VCDlg::IDD, pParent)
	, m_iTimeOut(5)
{
	m_hIcon = AfxGetApp()->LoadIcon(IDR_MAINFRAME);
	memset(ppbyTemp, 0, sizeof(ppbyTemp));
	nTemp = 0;
}

void CFPModule_VCDlg::DoDataExchange(CDataExchange* pDX)
{
	CDialogEx::DoDataExchange(pDX);
	DDX_Text(pDX, IDC_EDIT1, m_iTimeOut);
	DDV_MinMaxInt(pDX, m_iTimeOut, 1, 60);
}

BEGIN_MESSAGE_MAP(CFPModule_VCDlg, CDialogEx)
	ON_WM_SYSCOMMAND()
	ON_WM_PAINT()
	ON_WM_QUERYDRAGICON()
	ON_BN_CLICKED(IDC_OpenDevice, &CFPModule_VCDlg::OnBnClickedOpendevice)
	ON_BN_CLICKED(IDC_GetSDKVersion, &CFPModule_VCDlg::OnBnClickedGetsdkversion)
	ON_BN_CLICKED(IDC_GetDeviceInfo, &CFPModule_VCDlg::OnBnClickedGetdeviceinfo)
	ON_BN_CLICKED(IDC_SetTimeout, &CFPModule_VCDlg::OnBnClickedSettimeout)
	ON_BN_CLICKED(IDC_GetTimeout, &CFPModule_VCDlg::OnBnClickedGettimeout)
	ON_BN_CLICKED(IDC_DetectFinger, &CFPModule_VCDlg::OnBnClickedDetectfinger)
	ON_BN_CLICKED(IDC_CaptureImage, &CFPModule_VCDlg::OnBnClickedCaptureimage)
	ON_BN_CLICKED(IDC_FpEnroll, &CFPModule_VCDlg::OnBnClickedFpenroll)
	ON_BN_CLICKED(IDC_CloseDevice, &CFPModule_VCDlg::OnBnClickedClosedevice)
	ON_BN_CLICKED(IDC_FpEnroll2, &CFPModule_VCDlg::OnBnClickedFpMatch)
END_MESSAGE_MAP()

CFPModule_VCDlg *gCFPModule_VCDlg;
void  __stdcall FpMessageCallback(FP_MSG_TYPE_T enMsgType, void *pMsgData);
// CFPModule_VCDlg 消息处理程序
BOOL CFPModule_VCDlg::OnInitDialog()
{
	CDialogEx::OnInitDialog();

	// 将“关于...”菜单项添加到系统菜单中。

	// IDM_ABOUTBOX 必须在系统命令范围内。
	ASSERT((IDM_ABOUTBOX & 0xFFF0) == IDM_ABOUTBOX);
	ASSERT(IDM_ABOUTBOX < 0xF000);

	CMenu* pSysMenu = GetSystemMenu(FALSE);
	if (pSysMenu != NULL)
	{
		BOOL bNameValid;
		CString strAboutMenu;
		bNameValid = strAboutMenu.LoadString(IDS_ABOUTBOX);
		ASSERT(bNameValid);
		if (!strAboutMenu.IsEmpty())
		{
			pSysMenu->AppendMenu(MF_SEPARATOR);
			pSysMenu->AppendMenu(MF_STRING, IDM_ABOUTBOX, strAboutMenu);
		}
	}

	// 设置此对话框的图标。  当应用程序主窗口不是对话框时，框架将自动
	//  执行此操作
	SetIcon(m_hIcon, TRUE);			// 设置大图标
	SetIcon(m_hIcon, FALSE);		// 设置小图标
	
	
	gCFPModule_VCDlg = this;

	FpMessageHandler mCallBack = FpMessageCallback;
	FPModule_InstallMessageHandler(mCallBack);

	return TRUE;  // 除非将焦点设置到控件，否则返回 TRUE
}

void CFPModule_VCDlg::OnSysCommand(UINT nID, LPARAM lParam)
{
	if ((nID & 0xFFF0) == IDM_ABOUTBOX)
	{
		CAboutDlg dlgAbout;
		dlgAbout.DoModal();
	}
	else
	{
		CDialogEx::OnSysCommand(nID, lParam);
	}
}

// 如果向对话框添加最小化按钮，则需要下面的代码
//  来绘制该图标。  对于使用文档/视图模型的 MFC 应用程序，
//  这将由框架自动完成。

void CFPModule_VCDlg::OnPaint()
{
	static int isFirst = 0;
	if (IsIconic())
	{
		CPaintDC dc(this); // 用于绘制的设备上下文

		SendMessage(WM_ICONERASEBKGND, reinterpret_cast<WPARAM>(dc.GetSafeHdc()), 0);

		// 使图标在工作区矩形中居中
		int cxIcon = GetSystemMetrics(SM_CXICON);
		int cyIcon = GetSystemMetrics(SM_CYICON);
		CRect rect;
		GetClientRect(&rect);
		int x = (rect.Width() - cxIcon + 1) / 2;
		int y = (rect.Height() - cyIcon + 1) / 2;

		// 绘制图标
		dc.DrawIcon(x, y, m_hIcon);
	}
	else
	{
		if (isFirst == 0)
			ShowLocalBmp();
		isFirst = 1;
		CDialogEx::OnPaint();

	}
}

//当用户拖动最小化窗口时系统调用此函数取得光标
//显示。
HCURSOR CFPModule_VCDlg::OnQueryDragIcon()
{
	return static_cast<HCURSOR>(m_hIcon);
}
void ProcessMessage()
{
	MSG msg;
	::PeekMessage(&msg, NULL, 0, 0, PM_REMOVE);
	::DispatchMessage(&msg);
}
void CFPModule_VCDlg::SetDlgItemTextEX(CString Msg)
{
	CString oldStr;
	GetDlgItemText(IDC_MSG, oldStr);
	if (oldStr.Compare(Msg))
	{
		SetDlgItemText(IDC_MSG, Msg);
		ProcessMessage();
	}
}
 void  __stdcall FpMessageCallback(FP_MSG_TYPE_T enMsgType, void *pMsgData)
{
	static int index = 0;
	CString Msg;
	char fileName[64] = { 0 };
	if (enMsgType == FP_MSG_RISE_FINGER)
	{
		gCFPModule_VCDlg->SetDlgItemTextEX(_T("Lift and rest your finger"));
	}
	else if (enMsgType == FP_MSG_PRESS_FINGER)
	{
		gCFPModule_VCDlg->SetDlgItemTextEX(_T("Place Your Finger"));
	}
	else if (enMsgType == FP_MSG_ENROLL_TIME)
	{
		index = *((int *)pMsgData);
		Msg.Format(_T("Enroll Time:%d"), index);
		gCFPModule_VCDlg->SetDlgItemTextEX(Msg);
	}
	else if (enMsgType == FP_MSG_CAPTURED_IMAGE)
	{
		FP_IMAGE_DATA mSt;
		memcpy(&mSt, pMsgData, sizeof(FP_IMAGE_DATA));
		
		//转换成BMP
		gCFPModule_VCDlg->ImgRaw2BMP(mSt.pbyImage, gCFPModule_VCDlg->pbyBmpImage, mSt.dwWidth, mSt.dwHeight);
		
		//保存
		sprintf(fileName, "Enroll_%d.bmp", index);
		gCFPModule_VCDlg->SaveFile(fileName, gCFPModule_VCDlg->pbyBmpImage, mSt.dwWidth*mSt.dwHeight + FP_BMP_HEADER);

		// 在界面显示
		gCFPModule_VCDlg->ShowFPBmp(mSt.dwWidth, mSt.dwHeight, gCFPModule_VCDlg->pbyBmpImage);
	}
}
//原数数据转换成BMP
int CFPModule_VCDlg::ImgRaw2BMP(unsigned char* pImage, unsigned char* pBmp, int iWidth, int iHeight)
{
	unsigned int num;
	int i, j;
	unsigned char head[1078] = {
		/***************************/
		//file header
		0x42, 0x4d,//file type 
		//0x36,0x6c,0x01,0x00, //file size***
		0x0, 0x0, 0x0, 0x00, //file size***
		0x00, 0x00, //reserved
		0x00, 0x00,//reserved
		0x36, 0x4, 0x00, 0x00,//head byte***
		/***************************/
		//infoheader
		0x28, 0x00, 0x00, 0x00,//struct size

		//0x00,0x01,0x00,0x00,//map width*** 
		0x00, 0x00, 0x00, 0x00,//map width*** 
		//0x68,0x01,0x00,0x00,//map height***
		0x00, 0x00, 0x00, 0x00,//map height***

		0x01, 0x00,//must be 1
		0x08, 0x00,//color count***
		0x00, 0x00, 0x00, 0x00, //compression
		//0x00,0x68,0x01,0x00,//data size***
		0x00, 0x00, 0x00, 0x00,//data size***
		0x00, 0x00, 0x00, 0x00, //dpix
		0x00, 0x00, 0x00, 0x00, //dpiy
		0x00, 0x00, 0x00, 0x00,//color used
		0x00, 0x00, 0x00, 0x00,//color important

	};

	//确定图象宽度数值
	num = iWidth; head[18] = num & 0xFF;
	num = num >> 8;  head[19] = num & 0xFF;
	num = num >> 8;  head[20] = num & 0xFF;
	num = num >> 8;  head[21] = num & 0xFF;
	//确定图象高度数值
	num = iHeight; head[22] = num & 0xFF;
	num = num >> 8;  head[23] = num & 0xFF;
	num = num >> 8;  head[24] = num & 0xFF;
	num = num >> 8;  head[25] = num & 0xFF;

	//确定调色板数值
	j = 0;
	for (i = 54; i<1078; i = i + 4)
	{
		head[i] = head[i + 1] = head[i + 2] = j;
		head[i + 3] = 0;
		j++;
	}

	// 写BMP头
	memcpy(pBmp, head, 1078 * sizeof(char));

	//写入图象数据
	for (i = 0; i<iHeight; i++)
	{
		int pos = 1078 * sizeof(char) + (iHeight - 1 - i)*iWidth;
		memcpy(&pBmp[pos], &pImage[i*iWidth], iWidth*sizeof(char));
	}
}
int CFPModule_VCDlg::SaveFile(char* pName, unsigned char* pData, int iLenth)
{
	FILE *fp;
	fp = fopen(pName, "wb");
	fwrite(pData, 1, iLenth, fp);
	fclose(fp);
	return 0;
}
void CFPModule_VCDlg::ShowFPBmp(int width, int height, unsigned char *pbyBmp)
{

	int IDC = IDC_FPIMG;
	CImage img;
	COleStreamFile osf;
	osf.CreateMemoryStream(NULL);
	osf.Write((void*)pbyBmp, width * height + 1078);
	osf.SeekToBegin();
	img.Load(osf.GetStream());

	CRect rect;
	CRect rect1;
	//获取Picture Control控件的大小
	GetDlgItem(IDC)->GetWindowRect(&rect);
	//将客户区选中到控件表示的矩形区域内 ?
	ScreenToClient(&rect);
	GetDlgItem(IDC)->GetClientRect(&rect);
	CWnd *pWnd = NULL;
	pWnd = GetDlgItem(IDC);//获取控件句柄 ?
	pWnd->GetClientRect(&rect);//获取句柄指向控件区域的大小 ?
	CDC *pDc = NULL;
	pDc = pWnd->GetDC();//获取picture的DC ?
	SetStretchBltMode(pDc->m_hDC, STRETCH_HALFTONE);
	if (img.GetWidth() <= rect.Width() && img.GetHeight() <= rect.Width()) //图片小于控件区域，不缩放
	{
		rect1 = CRect(rect.TopLeft(), CSize(img.GetWidth(), img.GetHeight()));
		img.StretchBlt(pDc->m_hDC, rect1, SRCCOPY); //将图片画到Picture控件表示的矩形区域
	}
	else{
		float xScale = (float)rect.Width() / (float)img.GetWidth();
		float yScale = (float)rect.Height() / (float)img.GetHeight();
		float ScaleIndex = (xScale <= yScale ? xScale : yScale);
		rect1 = CRect(rect.TopLeft(), CSize((int)img.GetWidth()*ScaleIndex, (int)img.GetHeight()*ScaleIndex));
		img.StretchBlt(pDc->m_hDC, rect1, SRCCOPY); //将图片画到Picture控件表示的矩形区域
	}
	img.Draw(pDc->m_hDC, rect);//将图片绘制到picture表示的区域内 ?
	ReleaseDC(pDc);

	ProcessMessage();
}
void CFPModule_VCDlg::ShowLocalBmp()
{

	int IDC = IDC_FPIMG;
	unsigned char pbmpImage[256 * 360 + 1078];

	CImage img;
	img.LoadFromResource(AfxGetInstanceHandle(), MAKEINTRESOURCE(IDB_BITMAP1));
	//img.Load(_T("backup.bmp"));
	CRect rect;
	CRect rect1;
	//获取Picture Control控件的大小
	GetDlgItem(IDC)->GetWindowRect(&rect);
	//将客户区选中到控件表示的矩形区域内 ?
	ScreenToClient(&rect);
	GetDlgItem(IDC)->GetClientRect(&rect);
	CWnd *pWnd = NULL;
	pWnd = GetDlgItem(IDC);//获取控件句柄 ?
	pWnd->GetClientRect(&rect);//获取句柄指向控件区域的大小 ?
	CDC *pDc = NULL;
	pDc = pWnd->GetDC();//获取picture的DC ?
	SetStretchBltMode(pDc->m_hDC, STRETCH_HALFTONE);
	if (img.GetWidth() <= rect.Width() && img.GetHeight() <= rect.Width()) //图片小于控件区域，不缩放
	{
		rect1 = CRect(rect.TopLeft(), CSize(img.GetWidth(), img.GetHeight()));
		img.StretchBlt(pDc->m_hDC, rect1, SRCCOPY); //将图片画到Picture控件表示的矩形区域
	}
	else{
		float xScale = (float)rect.Width() / (float)img.GetWidth();
		float yScale = (float)rect.Height() / (float)img.GetHeight();
		float ScaleIndex = (xScale <= yScale ? xScale : yScale);
		rect1 = CRect(rect.TopLeft(), CSize((int)img.GetWidth()*ScaleIndex, (int)img.GetHeight()*ScaleIndex));
		img.StretchBlt(pDc->m_hDC, rect1, SRCCOPY); //将图片画到Picture控件表示的矩形区域
	}
	img.Draw(pDc->m_hDC, rect);//将图片绘制到picture表示的区域内 ?
	ReleaseDC(pDc);

	ProcessMessage();
}

void CFPModule_VCDlg::OnBnClickedOpendevice()
{
	int iRet;
	iRet = FPModule_OpenDevice();
	if (iRet == FP_SUCCESS)
	{
		SetDlgItemTextEX(_T("OpenDevice OK"));
	}
	else
	{
		MessageBox(_T("OpenDevice failed"));
	}
}


void CFPModule_VCDlg::OnBnClickedGetsdkversion()
{
	char info[64] = {0};
	CString Msg;
	int iRet = FPModule_GetSDKVersion(info);
	if (iRet == FP_SUCCESS)
	{
		Msg.Format(_T("%s"), info);
		SetDlgItemTextEX(Msg);
	}
	else
	{
		MessageBox(_T("GetSDKVersion failed"));
	}
}


void CFPModule_VCDlg::OnBnClickedGetdeviceinfo()
{
	char info[64] = { 0 };
	CString Msg;
	int iRet = FPModule_GetDeviceInfo(info);
	if (iRet == FP_SUCCESS)
	{
		Msg.Format(_T("%s"), info);
		SetDlgItemTextEX(Msg);
	}
	else
	{
		MessageBox(_T("GetDeviceInfo failed"));
	}
}


void CFPModule_VCDlg::OnBnClickedSettimeout()
{
	CString Msg;
	UpdateData(true);
	if (m_iTimeOut > 60 || m_iTimeOut < 1)
	{
		MessageBox(_T("SetTimeout failed(1~60)"));
		return;
	}
	int iRet = FPModule_SetTimeout(m_iTimeOut);
	if (iRet == FP_SUCCESS)
	{
		Msg.Format(_T("SetTimeout success:%d s"), m_iTimeOut);
		SetDlgItemTextEX(Msg);
	}
	else
	{
		MessageBox(_T("SetTimeout failed"));
	}
	UpdateData(false);
}


void CFPModule_VCDlg::OnBnClickedGettimeout()
{
	CString Msg;
	int iTime = 0;
	UpdateData(true);
	int iRet = FPModule_GetTimeout(&iTime);
	if (iRet == FP_SUCCESS)
	{
		Msg.Format(_T("GetTimeout success:%d s"), iTime);
		SetDlgItemTextEX(Msg);
	}
	else
	{
		MessageBox(_T("GetTimeout failed"));
	}
	m_iTimeOut = iTime;
	UpdateData(false);
}


void CFPModule_VCDlg::OnBnClickedDetectfinger()
{
	int pdwFpstatus = 0;
	CString Msg;
	int iRet = FPModule_DetectFinger(&pdwFpstatus);
	if (iRet == FP_SUCCESS)
	{
		Msg.Format(_T("DetectFinger success::%d "), pdwFpstatus);
		SetDlgItemTextEX(Msg);
		
	}
	else
	{
		MessageBox(_T("DetectFinger failed"));
	}
}
void CFPModule_VCDlg::GetTimeString(char *pTime)
{
	struct tm *newtime;
	time_t lt1;
	time(&lt1);
	newtime = localtime(&lt1);
	strftime(pTime, 32, "%Y%m%d%H%M%S", newtime);
	//strftime(pTime, 32, "Now it's %I:%M%p.", newtime);
}


void CFPModule_VCDlg::OnBnClickedCaptureimage()
{
	int w = 0, h = 0;
	CString Msg;
	char fileName[64] = { 0 };
	char szTime[32] = { 0 };
	GetTimeString(szTime);
	int iRet = FPModule_CaptureImage(pbyImage, &w, &h);
	if (iRet == FP_SUCCESS)
	{
		//转换成BMP
		ImgRaw2BMP(pbyImage, pbyBmpImage, w, h);

		//保存
		sprintf(fileName, "Capture_%s.bmp", szTime);
		SaveFile(fileName, gCFPModule_VCDlg->pbyBmpImage, w*h + FP_BMP_HEADER);

		// 在界面显示
		ShowFPBmp(w, h, pbyBmpImage);

		Msg.Format(_T("Capture_%s.bmp"), szTime);
		SetDlgItemTextEX(Msg);

	}
	else
	{
		MessageBox(_T("CaptureImage failed"));
	}
}


void CFPModule_VCDlg::OnBnClickedFpenroll()
{
	unsigned char data[FP_FTP_MAX] = {0};
	CString Msg;
	char fileName[64] = { 0 };
	FPModule_SetCollectTimes(0);
	int iRet = FPModule_FpEnroll(data);
	if (iRet == FP_SUCCESS)
	{
		Msg.Format(_T("Success Score:%d  ID:%d Cnt:%d"), FPModule_GetQuality(data), nTemp, nTemp+1);
		SetDlgItemTextEX(Msg);

		memcpy(ppbyTemp + nTemp*FP_FTP_MAX, data, FP_FTP_MAX);
		sprintf(fileName, "ftp_%d.dat", nTemp);
		SaveFile(fileName, data, FP_FTP_MAX);
		nTemp++;
	}
	else
	{
		MessageBox(_T("FpEnroll failed"));
	}
	Sleep(500);
	ShowLocalBmp();
}


void CFPModule_VCDlg::OnBnClickedClosedevice()
{
	int iRet = FPModule_CloseDevice();
	if (iRet == FP_SUCCESS)
	{
		SetDlgItemTextEX(_T("CloseDevice success"));
	}
	else
	{
		MessageBox(_T("CloseDevice failed"));
	}
}


void CFPModule_VCDlg::OnBnClickedFpMatch()
{
	unsigned char data[FP_FTP_MAX] = { 0 };
	CString Msg;
	char fileName[64] = { 0 };
	char szTime[32] = { 0 };
	GetTimeString(szTime);
	FPModule_SetCollectTimes(1);
	int iRet = FPModule_FpEnroll(data);
	if (iRet == FP_SUCCESS)
	{
	
		for (int i = 0; i < nTemp; i++)
		{
			if (FPModule_MatchTemplate(data, ppbyTemp + i*FP_FTP_MAX, 3) == FP_SUCCESS)
			{

				Msg.Format(_T("Match success ,ID::%d "), i);
				SetDlgItemTextEX(Msg);
				goto exit;
			}
		}

		Msg.Format(_T("MatchTemplate failed"));
		SetDlgItemTextEX(Msg);
		goto exit;
	}
	else
	{
		MessageBox(_T("MatchTemplate failed"));
	}
exit:
	Sleep(500);
	ShowLocalBmp();
}
