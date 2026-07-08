using System;
using System.Collections.Generic;
using System.IO;
using System.Net;
using System.Text;
using System.Text.RegularExpressions;

class EventStreamListener
{
    static string webhookUrl = "";

    static void Main(string[] args)
    {
        if (args.Length < 4)
        {
            Console.Error.WriteLine("Usage: EventStreamListener.exe <ip> <port> <username> <password> [webhookUrl]");
            Environment.Exit(1);
        }

        string ip = args[0];
        int port = int.Parse(args[1]);
        string user = args[2];
        string pass = args[3];
        if (args.Length > 4) webhookUrl = args[4];

        string url = string.Format("http://{0}:{1}/ISAPI/Event/notification/alertStream", ip, port);
        Console.Error.WriteLine("Connecting to " + url + " ...");

        while (true)
        {
            try
            {
                var request = (HttpWebRequest)WebRequest.Create(url);
                request.Credentials = new NetworkCredential(user, pass);
                request.PreAuthenticate = true;
                request.Method = "GET";
                request.Timeout = System.Threading.Timeout.Infinite;
                request.KeepAlive = true;

                using (var response = (HttpWebResponse)request.GetResponse())
                using (var stream = response.GetResponseStream())
                using (var reader = new StreamReader(stream, Encoding.UTF8))
                {
                    Console.Error.WriteLine("Connected. Waiting for events...");

                    var buffer = new StringBuilder();
                    char[] chars = new char[4096];
                    int bytesRead;

                    while ((bytesRead = reader.Read(chars, 0, chars.Length)) > 0)
                    {
                        buffer.Append(chars, 0, bytesRead);
                        string current = buffer.ToString();

                        // Extract complete EventNotificationAlert elements
                        ProcessEvents(ref current);
                        buffer.Clear();
                        buffer.Append(current);
                    }
                }
            }
            catch (Exception ex)
            {
                Console.Error.WriteLine("Connection error: " + ex.Message);
            }

            Console.Error.WriteLine("Reconnecting in 5 seconds...");
            System.Threading.Thread.Sleep(5000);
        }
    }

    static void ProcessEvents(ref string text)
    {
        const string closeTag = "</EventNotificationAlert>";

        while (true)
        {
            int closeIdx = text.IndexOf(closeTag);
            if (closeIdx < 0) break;

            int openIdx = text.LastIndexOf("<EventNotificationAlert", closeIdx);
            if (openIdx < 0) break;

            int endIdx = closeIdx + closeTag.Length;
            string xml = text.Substring(openIdx, endIdx - openIdx);
            text = text.Substring(endIdx);

            string json = ParseEventXml(xml);
            if (json != null)
            {
                Console.WriteLine(json);
                ForwardToWebhook(json);
            }
        }
    }

    static string ParseEventXml(string xml)
    {
        var fields = new Dictionary<string, string>();
        fields["event"] = "access_event";

        string val = ExtractXmlValue(xml, "employeeNoString");
        if (!string.IsNullOrEmpty(val)) fields["pin"] = val;

        val = ExtractXmlValue(xml, "employeeName");
        if (!string.IsNullOrEmpty(val)) fields["name"] = val;

        val = ExtractXmlValue(xml, "eventType");
        if (!string.IsNullOrEmpty(val)) fields["eventType"] = val;

        val = ExtractXmlValue(xml, "eventTime");
        if (!string.IsNullOrEmpty(val)) fields["time"] = val;

        val = ExtractXmlValue(xml, "doorNo");
        if (!string.IsNullOrEmpty(val)) fields["doorNo"] = val;

        // Parse minorEventType to determine in/out
        val = ExtractXmlValue(xml, "minorEventType");
        if (!string.IsNullOrEmpty(val))
        {
            fields["minorEventType"] = val;
            int minor = 0;
            if (int.TryParse(val, out minor))
            {
                if (minor == 75 || minor == 77 || minor == 79 || minor == 81 || minor == 83)
                    fields["status"] = "in";
                else if (minor == 76 || minor == 78 || minor == 80 || minor == 82 || minor == 84)
                    fields["status"] = "out";
                else
                    fields["status"] = "unknown";
            }
        }

        if (!fields.ContainsKey("pin") || string.IsNullOrEmpty(fields["pin"]))
            return null;

        var sb = new StringBuilder("{");
        bool first = true;
        foreach (var kv in fields)
        {
            if (!first) sb.Append(",");
            first = false;
            string v = (kv.Value ?? "").Replace("\\", "\\\\").Replace("\"", "\\\"");
            sb.AppendFormat("\"{0}\":\"{1}\"", kv.Key, v);
        }
        sb.Append("}");
        return sb.ToString();
    }

    static string ExtractXmlValue(string xml, string tag)
    {
        string pattern = string.Format("<{0}[^>]*>(.*?)</{0}>", tag);
        var m = Regex.Match(xml, pattern, RegexOptions.Singleline);
        return m.Success ? m.Groups[1].Value.Trim() : "";
    }

    static void ForwardToWebhook(string json)
    {
        if (string.IsNullOrEmpty(webhookUrl)) return;

        try
        {
            var data = Encoding.UTF8.GetBytes(json);
            var request = (HttpWebRequest)WebRequest.Create(webhookUrl);
            request.Method = "POST";
            request.ContentType = "application/json";
            request.ContentLength = data.Length;
            request.Timeout = 10000;
            using (var stream = request.GetRequestStream())
            {
                stream.Write(data, 0, data.Length);
            }
            using (var response = (HttpWebResponse)request.GetResponse()) { }
        }
        catch
        {
            // Silently fail - will retry on next event
        }
    }
}
