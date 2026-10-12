namespace Demo
{
    partial class Demo
    {
        /// <summary>
        /// 必需的设计器变量。
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        /// 清理所有正在使用的资源。
        /// </summary>
        /// <param name="disposing">如果应释放托管资源，为 true；否则为 false。</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Windows 窗体设计器生成的代码

        /// <summary>
        /// 设计器支持所需的方法 - 不要
        /// 使用代码编辑器修改此方法的内容。
        /// </summary>
        private void InitializeComponent()
        {
            System.ComponentModel.ComponentResourceManager resources = new System.ComponentModel.ComponentResourceManager(typeof(Demo));
            this.FPModule_OpenDevice = new System.Windows.Forms.Button();
            this.FPModule_CloseDevice = new System.Windows.Forms.Button();
            this.FPModule_GetSDKVersion = new System.Windows.Forms.Button();
            this.FPModule_GetDeviceInfo = new System.Windows.Forms.Button();
            this.FPModule_SetTimeout = new System.Windows.Forms.Button();
            this.FPModule_DetectFinger = new System.Windows.Forms.Button();
            this.FPModule_CaptureImage = new System.Windows.Forms.Button();
            this.FPModule_FpEnroll = new System.Windows.Forms.Button();
            this.FPModule_GetTimeout = new System.Windows.Forms.Button();
            this.fpImageBox = new System.Windows.Forms.PictureBox();
            this.MsgBox = new System.Windows.Forms.Label();
            this.FPModule_MatchTemplate = new System.Windows.Forms.Button();
            ((System.ComponentModel.ISupportInitialize)(this.fpImageBox)).BeginInit();
            this.SuspendLayout();
            // 
            // FPModule_OpenDevice
            // 
            this.FPModule_OpenDevice.Font = new System.Drawing.Font("Courier New", 9.75F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.FPModule_OpenDevice.Location = new System.Drawing.Point(32, 37);
            this.FPModule_OpenDevice.Name = "FPModule_OpenDevice";
            this.FPModule_OpenDevice.Size = new System.Drawing.Size(264, 36);
            this.FPModule_OpenDevice.TabIndex = 0;
            this.FPModule_OpenDevice.Text = "FPModule_OpenDevice";
            this.FPModule_OpenDevice.UseVisualStyleBackColor = true;
            this.FPModule_OpenDevice.Click += new System.EventHandler(this.FPModule_OpenDevice_Click);
            // 
            // FPModule_CloseDevice
            // 
            this.FPModule_CloseDevice.Font = new System.Drawing.Font("Courier New", 9.75F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.FPModule_CloseDevice.Location = new System.Drawing.Point(32, 420);
            this.FPModule_CloseDevice.Name = "FPModule_CloseDevice";
            this.FPModule_CloseDevice.Size = new System.Drawing.Size(264, 36);
            this.FPModule_CloseDevice.TabIndex = 1;
            this.FPModule_CloseDevice.Text = "FPModule_CloseDevice";
            this.FPModule_CloseDevice.UseVisualStyleBackColor = true;
            this.FPModule_CloseDevice.Click += new System.EventHandler(this.FPModule_CloseDevice_Click);
            // 
            // FPModule_GetSDKVersion
            // 
            this.FPModule_GetSDKVersion.Font = new System.Drawing.Font("Courier New", 9.75F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.FPModule_GetSDKVersion.Location = new System.Drawing.Point(32, 79);
            this.FPModule_GetSDKVersion.Name = "FPModule_GetSDKVersion";
            this.FPModule_GetSDKVersion.Size = new System.Drawing.Size(264, 36);
            this.FPModule_GetSDKVersion.TabIndex = 2;
            this.FPModule_GetSDKVersion.Text = "FPModule_GetSDKVersion";
            this.FPModule_GetSDKVersion.UseVisualStyleBackColor = true;
            this.FPModule_GetSDKVersion.Click += new System.EventHandler(this.FPModule_GetSDKVersion_Click);
            // 
            // FPModule_GetDeviceInfo
            // 
            this.FPModule_GetDeviceInfo.Font = new System.Drawing.Font("Courier New", 9.75F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.FPModule_GetDeviceInfo.Location = new System.Drawing.Point(32, 121);
            this.FPModule_GetDeviceInfo.Name = "FPModule_GetDeviceInfo";
            this.FPModule_GetDeviceInfo.Size = new System.Drawing.Size(264, 36);
            this.FPModule_GetDeviceInfo.TabIndex = 3;
            this.FPModule_GetDeviceInfo.Text = "FPModule_GetDeviceInfo";
            this.FPModule_GetDeviceInfo.UseVisualStyleBackColor = true;
            this.FPModule_GetDeviceInfo.Click += new System.EventHandler(this.FPModule_GetDeviceInfo_Click);
            // 
            // FPModule_SetTimeout
            // 
            this.FPModule_SetTimeout.Font = new System.Drawing.Font("Courier New", 9.75F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.FPModule_SetTimeout.Location = new System.Drawing.Point(32, 163);
            this.FPModule_SetTimeout.Name = "FPModule_SetTimeout";
            this.FPModule_SetTimeout.Size = new System.Drawing.Size(264, 36);
            this.FPModule_SetTimeout.TabIndex = 4;
            this.FPModule_SetTimeout.Text = "FPModule_SetTimeout";
            this.FPModule_SetTimeout.UseVisualStyleBackColor = true;
            this.FPModule_SetTimeout.Click += new System.EventHandler(this.FPModule_SetTimeout_Click);
            // 
            // FPModule_DetectFinger
            // 
            this.FPModule_DetectFinger.Font = new System.Drawing.Font("Courier New", 9.75F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.FPModule_DetectFinger.Location = new System.Drawing.Point(32, 247);
            this.FPModule_DetectFinger.Name = "FPModule_DetectFinger";
            this.FPModule_DetectFinger.Size = new System.Drawing.Size(264, 36);
            this.FPModule_DetectFinger.TabIndex = 5;
            this.FPModule_DetectFinger.Text = "FPModule_DetectFinger";
            this.FPModule_DetectFinger.UseVisualStyleBackColor = true;
            this.FPModule_DetectFinger.Click += new System.EventHandler(this.FPModule_DetectFinger_Click);
            // 
            // FPModule_CaptureImage
            // 
            this.FPModule_CaptureImage.Font = new System.Drawing.Font("Courier New", 9.75F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.FPModule_CaptureImage.Location = new System.Drawing.Point(32, 289);
            this.FPModule_CaptureImage.Name = "FPModule_CaptureImage";
            this.FPModule_CaptureImage.Size = new System.Drawing.Size(264, 36);
            this.FPModule_CaptureImage.TabIndex = 6;
            this.FPModule_CaptureImage.Text = "FPModule_CaptureImage";
            this.FPModule_CaptureImage.UseVisualStyleBackColor = true;
            this.FPModule_CaptureImage.Click += new System.EventHandler(this.FPModule_CaptureImage_Click);
            // 
            // FPModule_FpEnroll
            // 
            this.FPModule_FpEnroll.Font = new System.Drawing.Font("Courier New", 9.75F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.FPModule_FpEnroll.Location = new System.Drawing.Point(32, 331);
            this.FPModule_FpEnroll.Name = "FPModule_FpEnroll";
            this.FPModule_FpEnroll.Size = new System.Drawing.Size(264, 36);
            this.FPModule_FpEnroll.TabIndex = 7;
            this.FPModule_FpEnroll.Text = "FPModule_FpEnroll";
            this.FPModule_FpEnroll.UseVisualStyleBackColor = true;
            this.FPModule_FpEnroll.Click += new System.EventHandler(this.FPModule_FpEnroll_Click);
            // 
            // FPModule_GetTimeout
            // 
            this.FPModule_GetTimeout.Font = new System.Drawing.Font("Courier New", 9.75F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.FPModule_GetTimeout.Location = new System.Drawing.Point(32, 205);
            this.FPModule_GetTimeout.Name = "FPModule_GetTimeout";
            this.FPModule_GetTimeout.Size = new System.Drawing.Size(264, 36);
            this.FPModule_GetTimeout.TabIndex = 9;
            this.FPModule_GetTimeout.Text = "FPModule_GetTimeout";
            this.FPModule_GetTimeout.UseVisualStyleBackColor = true;
            this.FPModule_GetTimeout.Click += new System.EventHandler(this.FPModule_GetTimeout_Click);
            // 
            // fpImageBox
            // 
            this.fpImageBox.BackgroundImage = global::Demo.Properties.Resources.backup;
            this.fpImageBox.Location = new System.Drawing.Point(347, 37);
            this.fpImageBox.Name = "fpImageBox";
            this.fpImageBox.Size = new System.Drawing.Size(257, 288);
            this.fpImageBox.SizeMode = System.Windows.Forms.PictureBoxSizeMode.StretchImage;
            this.fpImageBox.TabIndex = 10;
            this.fpImageBox.TabStop = false;
            // 
            // MsgBox
            // 
            this.MsgBox.Font = new System.Drawing.Font("Courier New", 9.75F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.MsgBox.ForeColor = System.Drawing.Color.Blue;
            this.MsgBox.Location = new System.Drawing.Point(316, 373);
            this.MsgBox.Name = "MsgBox";
            this.MsgBox.Size = new System.Drawing.Size(320, 42);
            this.MsgBox.TabIndex = 11;
            this.MsgBox.Text = "Welcome";
            this.MsgBox.TextAlign = System.Drawing.ContentAlignment.TopCenter;
            // 
            // FPModule_MatchTemplate
            // 
            this.FPModule_MatchTemplate.Font = new System.Drawing.Font("Courier New", 9.75F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.FPModule_MatchTemplate.Location = new System.Drawing.Point(32, 373);
            this.FPModule_MatchTemplate.Name = "FPModule_MatchTemplate";
            this.FPModule_MatchTemplate.Size = new System.Drawing.Size(264, 36);
            this.FPModule_MatchTemplate.TabIndex = 12;
            this.FPModule_MatchTemplate.Text = "FPModule_MatchTemplate";
            this.FPModule_MatchTemplate.UseVisualStyleBackColor = true;
            this.FPModule_MatchTemplate.Click += new System.EventHandler(this.FPModule_MatchTemplate_Click);
            // 
            // Demo
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(6F, 12F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(648, 484);
            this.Controls.Add(this.FPModule_MatchTemplate);
            this.Controls.Add(this.MsgBox);
            this.Controls.Add(this.fpImageBox);
            this.Controls.Add(this.FPModule_GetTimeout);
            this.Controls.Add(this.FPModule_FpEnroll);
            this.Controls.Add(this.FPModule_CaptureImage);
            this.Controls.Add(this.FPModule_DetectFinger);
            this.Controls.Add(this.FPModule_SetTimeout);
            this.Controls.Add(this.FPModule_GetDeviceInfo);
            this.Controls.Add(this.FPModule_GetSDKVersion);
            this.Controls.Add(this.FPModule_CloseDevice);
            this.Controls.Add(this.FPModule_OpenDevice);
            this.Icon = ((System.Drawing.Icon)(resources.GetObject("$this.Icon")));
            this.Name = "Demo";
            this.Text = "Demo";
            ((System.ComponentModel.ISupportInitialize)(this.fpImageBox)).EndInit();
            this.ResumeLayout(false);

        }

        #endregion

        private System.Windows.Forms.Button FPModule_OpenDevice;
        private System.Windows.Forms.Button FPModule_CloseDevice;
        private System.Windows.Forms.Button FPModule_GetSDKVersion;
        private System.Windows.Forms.Button FPModule_GetDeviceInfo;
        private System.Windows.Forms.Button FPModule_SetTimeout;
        private System.Windows.Forms.Button FPModule_DetectFinger;
        private System.Windows.Forms.Button FPModule_CaptureImage;
        private System.Windows.Forms.Button FPModule_FpEnroll;
        private System.Windows.Forms.Button FPModule_GetTimeout;
        private System.Windows.Forms.PictureBox fpImageBox;
        private System.Windows.Forms.Label MsgBox;
        private System.Windows.Forms.Button FPModule_MatchTemplate;
    }
}

