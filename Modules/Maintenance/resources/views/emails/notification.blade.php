<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notificationType === 'new' ? 'New Maintenance Request' : 'Maintenance Status Update' }}</title>
    <style>
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table, td {
            mso-table-lspace: 0;
            mso-table-rspace: 0;
        }
        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            height: 100% !important;
        }
        .ExternalClass, .ReadMsgBody {
            width: 100%;
        }
        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass td, .ExternalClass div {
            line-height: 100%;
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#F2EFEA; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F2EFEA; padding:30px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%;">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#1A1A1A; padding:30px 30px; text-align:center;">
                            <!--[if mso]>
                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px;">
                            <v:fill type="gradient" color="#1A1A1A" color2="#2D2D2D" angle="135" />
                            <v:textbox style="mso-fit-shape-to-text:true" inset="0,0,0,0">
                            <![endif]-->
                            <h1 style="color:#C9A962; font-size:24px; font-weight:300; letter-spacing:5px; text-transform:uppercase; margin:0 0 3px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Brickspoint</h1>
                            <p style="color:#888888; font-size:10px; letter-spacing:3px; text-transform:uppercase; margin:0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Maintenance Department</p>
                            <!--[if mso]>
                            </v:textbox>
                            </v:rect>
                            <![endif]-->
                        </td>
                    </tr>

                    <!-- Alert Banner -->
                    <tr>
                        <td style="{{ $notificationType === 'new' ? 'background-color:#DC2626;' : 'background-color:#C9A962;' }} padding:16px 20px; text-align:center;">
                            <!--[if mso]>
                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px;">
                            <v:fill type="gradient" color="{{ $notificationType === 'new' ? '#EF4444' : '#C9A962' }}" color2="{{ $notificationType === 'new' ? '#DC2626' : '#D4B978' }}" angle="0" />
                            <v:textbox style="mso-fit-shape-to-text:true" inset="0,0,0,0">
                            <![endif]-->
                            <p style="margin:0; color:#FFFFFF; font-size:13px; font-weight:700; letter-spacing:0.5px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                @if($notificationType === 'new')
                                    &#9888; New Maintenance Request Submitted
                                @else
                                    &#128203; Maintenance Status Updated
                                @endif
                            </p>
                            <!--[if mso]>
                            </v:textbox>
                            </v:rect>
                            <![endif]-->
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="background-color:#FFFFFF; padding:35px 30px 20px 30px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                        <p style="color:#666666; font-size:14px; margin:0 0 25px 0; line-height:1.6;">
                                            @if($notificationType === 'new')
                                                A new maintenance request has been logged and requires attention.
                                            @else
                                                The status of a maintenance request has been updated.
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Status Change -->
                            @if($notificationType === 'status_update' && $previousStatus)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FAF8F5; border-left:4px solid #C9A962; margin-bottom:25px;">
                                <tr>
                                    <td style="padding:18px 20px; text-align:center;">
                                        <p style="font-size:11px; color:#888888; text-transform:uppercase; letter-spacing:1px; margin:0 0 12px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Status Change</p>
                                        <table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="padding:4px 12px; background-color:{{ $previousStatus === 'new' ? '#FEE2E2' : ($previousStatus === 'in_progress' ? '#FEF3C7' : ($previousStatus === 'completed' ? '#D1FAE5' : '#E5E7EB')) }}; color:{{ $previousStatus === 'new' ? '#991B1B' : ($previousStatus === 'in_progress' ? '#92400E' : ($previousStatus === 'completed' ? '#065F46' : '#374151')) }}; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">{{ str_replace('_', ' ', ucfirst($previousStatus)) }}</td>
                                                <td style="padding:0 10px; font-size:18px; color:#C9A962; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">&#8594;</td>
                                                <td style="padding:4px 12px; background-color:{{ $log->status === 'new' ? '#FEE2E2' : ($log->status === 'in_progress' ? '#FEF3C7' : ($log->status === 'completed' ? '#D1FAE5' : '#E5E7EB')) }}; color:{{ $log->status === 'new' ? '#991B1B' : ($log->status === 'in_progress' ? '#92400E' : ($log->status === 'completed' ? '#065F46' : '#374151')) }}; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">{{ str_replace('_', ' ', ucfirst($log->status)) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <!-- Request Details -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FAF8F5; margin-bottom:25px;">
                                <tr>
                                    <td style="padding:24px 24px 0 24px;">
                                        <p style="font-size:12px; font-weight:600; color:#1A1A1A; text-transform:uppercase; letter-spacing:1px; margin:0 0 16px 0; padding-bottom:10px; border-bottom:2px solid #C9A962; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Request Details</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 24px 0 24px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="width:130px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:12px; color:#888888; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; padding:9px 0; border-bottom:1px solid #EDE8E1; vertical-align:top;">Location</td>
                                                <td style="font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:500; padding:9px 0; border-bottom:1px solid #EDE8E1;">{{ $log->location }}</td>
                                            </tr>
                                            <tr>
                                                <td style="width:130px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:12px; color:#888888; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; padding:9px 0; border-bottom:1px solid #EDE8E1; vertical-align:top;">Reported On</td>
                                                <td style="font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:500; padding:9px 0; border-bottom:1px solid #EDE8E1;">{{ $log->complaint_datetime?->format('l, F d, Y \\a\\t h:i A') ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="width:130px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:12px; color:#888888; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; padding:9px 0; border-bottom:1px solid #EDE8E1; vertical-align:top;">Lodged By</td>
                                                <td style="font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:500; padding:9px 0; border-bottom:1px solid #EDE8E1;">{{ $log->lodged_by }}</td>
                                            </tr>
                                            @if($log->received_by)
                                            <tr>
                                                <td style="width:130px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:12px; color:#888888; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; padding:9px 0; border-bottom:1px solid #EDE8E1; vertical-align:top;">Received By</td>
                                                <td style="font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:500; padding:9px 0; border-bottom:1px solid #EDE8E1;">{{ $log->received_by }}</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td style="width:130px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:12px; color:#888888; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; padding:9px 0; border-bottom:1px solid #EDE8E1; vertical-align:top;">Current Status</td>
                                                <td style="font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:500; padding:9px 0; border-bottom:1px solid #EDE8E1;">
                                                    <!--[if mso]>
                                                    <span style="padding:3px 10px; background-color:{{ $log->status === 'new' ? '#FEE2E2' : ($log->status === 'in_progress' ? '#FEF3C7' : ($log->status === 'completed' ? '#D1FAE5' : '#E5E7EB')) }}; color:{{ $log->status === 'new' ? '#991B1B' : ($log->status === 'in_progress' ? '#92400E' : ($log->status === 'completed' ? '#065F46' : '#374151')) }}; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">{{ str_replace('_', ' ', ucfirst($log->status)) }}</span>
                                                    <![endif]-->
                                                    <!--[if !mso]><!-->
                                                    <span style="display:inline-block; padding:3px 10px; background-color:{{ $log->status === 'new' ? '#FEE2E2' : ($log->status === 'in_progress' ? '#FEF3C7' : ($log->status === 'completed' ? '#D1FAE5' : '#E5E7EB')) }}; color:{{ $log->status === 'new' ? '#991B1B' : ($log->status === 'in_progress' ? '#92400E' : ($log->status === 'completed' ? '#065F46' : '#374151')) }}; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">{{ str_replace('_', ' ', ucfirst($log->status)) }}</span>
                                                    <!--<![endif]-->
                                                </td>
                                            </tr>
                                            @if($log->cost_of_fixing)
                                            <tr>
                                                <td style="width:130px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:12px; color:#888888; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; padding:9px 0; border-bottom:1px solid #EDE8E1; vertical-align:top;">Cost of Fixing</td>
                                                <td style="font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#C9A962; font-weight:600; padding:9px 0; border-bottom:1px solid #EDE8E1;">&#8358;{{ number_format($log->cost_of_fixing, 2) }}</td>
                                            </tr>
                                            @endif
                                            @if($log->completion_date)
                                            <tr>
                                                <td style="width:130px; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:12px; color:#888888; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; padding:9px 0; vertical-align:top;">Completion Date</td>
                                                <td style="font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:13px; color:#2D2D2D; font-weight:500; padding:9px 0;">{{ $log->completion_date?->format('F d, Y') }}</td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                                <tr><td style="padding:0 0 20px 0;"></td></tr>
                            </table>

                            <!-- Nature of Complaint -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F8F6F3; margin-bottom:25px;">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <p style="font-size:11px; font-weight:600; color:#888888; text-transform:uppercase; letter-spacing:0.5px; margin:0 0 10px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">Nature of Complaint</p>
                                        <p style="color:#4A4A4A; font-size:13px; line-height:1.7; margin:0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">{!! nl2br(e($log->nature_of_complaint)) !!}</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:30px;">
                                <tr>
                                    <td align="center">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" style="background-color:#C9A962; padding:0;">
                                                    <!--[if mso]>
                                                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ route('maintenance.show', $log->id) }}" style="height:46px;v-text-anchor:middle;width:220px;" arcsize="10%" strokecolor="#B8942E" fillcolor="#C9A962">
                                                    <w:anchorlock/>
                                                    <center style="color:#FFFFFF; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px;">View Full Details</center>
                                                    </v:roundrect>
                                                    <![endif]-->
                                                    <!--[if !mso]><!-->
                                                    <a href="{{ route('maintenance.show', $log->id) }}" style="display:inline-block; padding:14px 32px; background-color:#C9A962; color:#FFFFFF; text-decoration:none; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px;">View Full Details</a>
                                                    <!--<![endif]-->
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Divider -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:30px 0 0 0;">
                                <tr>
                                    <td style="border-top:1px solid #EDE8E1;"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#1A1A1A; padding:28px 30px; text-align:center;">
                            <!--[if mso]>
                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px;">
                            <v:fill type="gradient" color="#1A1A1A" color2="#0D0D0D" angle="180" />
                            <v:textbox style="mso-fit-shape-to-text:true" inset="0,0,0,0">
                            <![endif]-->
                            <p style="color:#C9A962; font-size:14px; font-weight:300; letter-spacing:4px; margin:0 0 3px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">
                                <a href="home" style="color:#C9A962; text-decoration:none; font-weight:800; letter-spacing:-0.5px;">BRICKSPOINT<sup style="font-size:8px;">&trade;</sup></a>
                            </p>
                            <p style="color:#555555; font-size:9px; margin:0 0 15px 0; font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;">&copy; {{ date('Y') }} Brickspoint. All rights reserved.</p>
                            <!--[if mso]>
                            </v:textbox>
                            </v:rect>
                            <![endif]-->
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>