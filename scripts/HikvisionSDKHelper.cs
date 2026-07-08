using System;
using System.Collections.Generic;
using System.Runtime.InteropServices;
using System.Text;
using System.Threading;

class HikvisionSDKHelper
{
    const string HCNetSDK = @"HCNetSDK.dll";

    [DllImport(HCNetSDK)]
    static extern bool NET_DVR_Init();

    [DllImport(HCNetSDK)]
    static extern bool NET_DVR_SetConnectTime(uint dwWaitTime, uint dwTryTimes);

    [DllImport(HCNetSDK)]
    static extern bool NET_DVR_SetReconnect(uint dwInterval, bool bEnableRecon);

    [DllImport(HCNetSDK)]
    static extern int NET_DVR_Login_V40(ref NET_DVR_USER_LOGIN_INFO pLoginInfo, ref NET_DVR_DEVICEINFO_V40 lpDeviceInfo);

    [DllImport(HCNetSDK)]
    static extern bool NET_DVR_Logout(int lUserID);

    [DllImport(HCNetSDK)]
    static extern bool NET_DVR_Cleanup();

    [DllImport(HCNetSDK)]
    static extern uint NET_DVR_GetLastError();

    [DllImport(HCNetSDK, CallingConvention = CallingConvention.StdCall)]
    static extern int NET_DVR_StartListen_V30(int lUserID, int dwStartNode, ALARMCallBack cbAlarmData, IntPtr pUserData);

    [DllImport(HCNetSDK)]
    static extern bool NET_DVR_StopListen(int lHandle);

    [StructLayout(LayoutKind.Sequential)]
    struct NET_DVR_USER_LOGIN_INFO
    {
        [MarshalAs(UnmanagedType.ByValTStr, SizeConst = 64)]
        public string sDeviceAddress;
        public ushort wPort;
        [MarshalAs(UnmanagedType.ByValTStr, SizeConst = 64)]
        public string sUserName;
        [MarshalAs(UnmanagedType.ByValTStr, SizeConst = 64)]
        public string sPassword;
        public byte bUseAsynLogin;
        public byte bUseSpecifiedIP;
        [MarshalAs(UnmanagedType.ByValArray, SizeConst = 16)]
        public byte[] byRes;
        public byte bResType;
        [MarshalAs(UnmanagedType.ByValArray, SizeConst = 128)]
        public byte[] byRes1;
    }

    [StructLayout(LayoutKind.Sequential)]
    struct NET_DVR_DEVICEINFO_V40
    {
        [MarshalAs(UnmanagedType.ByValTStr, SizeConst = 32)]
        public string sSerialNumber;
        public byte byAlarmInPortNum;
        public byte byAlarmOutPortNum;
        public byte byDiskNum;
        public byte byDVRType;
        public byte byChanNum;
        public byte byStartChan;
        public byte byAudioChanNum;
        public byte byIPChanNum;
        public byte byRes1;
        [MarshalAs(UnmanagedType.ByValArray, SizeConst = 24)]
        public byte[] byRes2;
    }

    [StructLayout(LayoutKind.Sequential)]
    struct NET_DVR_TIME
    {
        public uint dwYear;
        public uint dwMonth;
        public uint dwDay;
        public uint dwHour;
        public uint dwMinute;
        public uint dwSecond;
    }

    [StructLayout(LayoutKind.Sequential)]
    struct NET_DVR_ACS_ALARM_INFO
    {
        public uint dwSize;
        public uint dwMajor;
        public uint dwMinor;
        [MarshalAs(UnmanagedType.ByValArray, SizeConst = 32)]
        public byte[] sCardNo;
        [MarshalAs(UnmanagedType.ByValArray, SizeConst = 32)]
        public byte[] sEmployeeNo;
        public NET_DVR_TIME struTime;
        // Remaining fields omitted for brevity
    }

    // Callback delegate - must be kept alive to avoid GC
    delegate bool ALARMCallBack(int lCommand, IntPtr pAlarmInfo, int pBuf, int dwBufLen, IntPtr pUser);

    static ALARMCallBack alarmCallback;
    static int loginId = -1;

    static void Main(string[] args)
    {
        if (args.Length < 4)
        {
            Console.Error.WriteLine("Usage: HikvisionSDKHelper.exe <ip> <port> <username> <password>");
            Environment.Exit(1);
        }

        string ip = args[0];
        int port = int.Parse(args[1]);
        string user = args[2];
        string pass = args[3];

        if (!NET_DVR_Init())
        {
            Console.Error.WriteLine(string.Format("NET_DVR_Init failed: error {0}", NET_DVR_GetLastError()));
            Environment.Exit(1);
        }

        NET_DVR_SetConnectTime(5000, 3);
        NET_DVR_SetReconnect(10000, true);

        var loginInfo = new NET_DVR_USER_LOGIN_INFO
        {
            sDeviceAddress = ip,
            wPort = (ushort)port,
            sUserName = user,
            sPassword = pass,
            bUseAsynLogin = 0,
            bResType = 0,
            byRes = new byte[16],
            byRes1 = new byte[128],
        };

        var deviceInfo = new NET_DVR_DEVICEINFO_V40();
        loginId = NET_DVR_Login_V40(ref loginInfo, ref deviceInfo);

        if (loginId < 0)
        {
            Console.Error.WriteLine(string.Format("Login failed: error {0}", NET_DVR_GetLastError()));
            NET_DVR_Cleanup();
            Environment.Exit(1);
        }

        // Output login success
        Console.WriteLine(ToJson(new Dictionary<string, string> {
            { "event", "connected" },
            { "serial", deviceInfo.sSerialNumber ?? "" },
            { "loginId", loginId.ToString() },
            { "timestamp", DateTime.Now.ToString("o") }
        }));

        // Register callback (store in static field to prevent GC)
        alarmCallback = new ALARMCallBack(OnAlarm);
        int handle = NET_DVR_StartListen_V30(loginId, 0, alarmCallback, IntPtr.Zero);

        if (handle < 0)
        {
            Console.Error.WriteLine(string.Format("StartListen failed: error {0}", NET_DVR_GetLastError()));
        }
        else
        {
            Console.WriteLine(ToJson(new Dictionary<string, string> {
                { "event", "listening" },
                { "handle", handle.ToString() },
                { "timestamp", DateTime.Now.ToString("o") }
            }));
        }

        Console.Error.WriteLine("SDK Helper started. Waiting for events...");

        // Keep alive
        while (true)
        {
            Thread.Sleep(1000);
        }
    }

    static bool OnAlarm(int lCommand, IntPtr pAlarmInfo, int pBuf, int dwBufLen, IntPtr pUser)
    {
        var evt = new Dictionary<string, string>();
        evt.Add("event", "alarm_received");
        evt.Add("command", lCommand.ToString());
        evt.Add("timestamp", DateTime.Now.ToString("o"));

        if (pAlarmInfo != IntPtr.Zero && dwBufLen > 0)
        {
            try
            {
                byte[] buffer = new byte[Math.Min(dwBufLen, 256)];
                Marshal.Copy(pAlarmInfo, buffer, 0, buffer.Length);
                evt.Add("data", BitConverter.ToString(buffer));
                evt.Add("dataLen", dwBufLen.ToString());
            }
            catch { }
        }

        Console.WriteLine(ToJson(evt));
        return true;
    }

    static string ToJson(Dictionary<string, string> dict)
    {
        var sb = new StringBuilder("{");
        bool first = true;
        foreach (var kv in dict)
        {
            if (!first) sb.Append(",");
            first = false;
            string val = (kv.Value ?? "").Replace("\\", "\\\\").Replace("\"", "\\\"");
            sb.AppendFormat("\"{0}\":\"{1}\"", kv.Key, val);
        }
        sb.Append("}");
        return sb.ToString();
    }
}
