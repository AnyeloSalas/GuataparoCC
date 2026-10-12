
// FPModule_VCDlg.h : 头文件
//

#pragma once
#include "FPModule_SDK.h"

// CFPModule_VCDlg 对话框
class CFPModule_VCDlg : public CDialogEx
{
// 构造
public:
	CFPModule_VCDlg(CWnd* pParent = NULL);	// 标准构造函数

// 对话框数据
	enum { IDD = IDD_FPMODULE_VC_DIALOG };

	protected:
	virtual void DoDataExchange(CDataExchange* pDX);	// DDX/DDV 支持


// 实现
protected:
	HICON m_hIcon;

	// 生成的消息映射函数
	virtual BOOL OnInitDialog();
	afx_msg void OnSysCommand(UINT nID, LPARAM lParam);
	afx_msg void OnPaint();
	afx_msg HCURSOR OnQueryDragIcon();
	DECLARE_MESSAGE_MAP()


	
public:
	unsigned char ppbyTemp[FP_FTP_MAX*1000];
	int nTemp;
	unsigned char pbyImage[FP_IMAGE_WIDTH * FP_IMAGE_HEIGHT];
	unsigned char pbyBmpImage[FP_IMAGE_WIDTH * FP_IMAGE_HEIGHT + FP_BMP_HEADER];
	afx_msg void OnBnClickedOpendevice();

	void ShowFPBmp(int width, int height, unsigned char *pbyBmpImage);
	void ShowLocalBmp();
	int ImgRaw2BMP(unsigned char* pImage, unsigned char* pBmp, int iWidth, int iHeight);
	void SetDlgItemTextEX(CString Msg);
	int SaveFile(char* pName, unsigned char* pData, int iLenth);

	void GetTimeString(char *pTime);

	afx_msg void OnBnClickedGetsdkversion();
	afx_msg void OnBnClickedGetdeviceinfo();
	afx_msg void OnBnClickedSettimeout();
	afx_msg void OnBnClickedGettimeout();
	afx_msg void OnBnClickedDetectfinger();
	afx_msg void OnBnClickedCaptureimage();
	afx_msg void OnBnClickedFpenroll();
	afx_msg void OnBnClickedClosedevice();
	afx_msg void OnBnClickedFpMatch();
	int m_iTimeOut;
};
