<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Function Sheet - {{ $order->order_id }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Lobster&family=Montserrat&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 10mm;

            /* Reduced from 15mm for more usable space */
            @top-center {
                content: "Banquet Management System";
                font-size: 8pt;
            }

            @bottom-center {
                content: "Confidential - Page " counter(page);
                font-size: 8pt;
            }
        }

        body {
            font-family: 'DejaVu Sans', 'Noto Sans', sans-serif, Verdana, Geneva, Tahoma;
            line-height: 1.2;
            /* Tightened from 1.4 */
            color: #333;
            font-size: 9pt;
            /* Reduced from 10pt */
            width: 190mm;
            /* Increased to 190mm with 10mm margins */
            margin: 0 auto;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            margin-bottom: 5mm;
            /* Reduced from 15mm */
            border-bottom: 1px solid #1a237e;
            /* Thinner border */
            padding-bottom: 5px;
        }

        h1 {
            color: #1a237e;
            font-size: 14pt;
            /* Reduced from 18pt */
            margin: 0 0 3px 0;
            letter-spacing: 0.5px;
        }

        .sub-header {
            font-size: 8pt;
            /* Reduced from 9pt */
            color: #666;
        }

        .section {
            margin: 5mm 0;
            /* Reduced from 8mm */
            page-break-inside: avoid;
        }

        .section-title {
            background: #1a237e;
            color: white;
            padding: 2px 6px;
            /* Reduced padding */
            font-size: 10pt;
            /* Reduced from 11pt */
            margin: 8px 0;
            /* Reduced from 12px */
            border-radius: 2px;
        }

        h3 {
            font-size: 10pt;
            margin: 5px 0;
            color: #1a237e;
        }

        table {
            width: 100%;
            max-width: 190mm;
            border-collapse: collapse;
            margin: 3px 0;
            /* Reduced from 5px 0 10px */
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #eee;
            padding: 4px;
            /* Reduced from 8px */
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        th {
            background: #f5f5f5;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 7pt;
            /* Reduced from 8pt */
        }

        /* Customer Table */
        .customer-table td:first-child {
            width: 40mm;
        }

        .customer-table td:last-child {
            width: 150mm;
        }

        /* Event Table */
        .event-table td:first-child {
            width: 40mm;
        }

        .event-table td:last-child {
            width: 150mm;
        }

        /* Menu Table */
        .menu-table th:nth-child(1),
        .menu-table td:nth-child(1) {
            width: 30mm;
        }

        .menu-table th:nth-child(2),
        .menu-table td:nth-child(2) {
            width: 80mm;
        }

        .menu-table th:nth-child(3),
        .menu-table td:nth-child(3) {
            width: 20mm;
        }

        .menu-table th:nth-child(4),
        .menu-table td:nth-child(4) {
            width: 30mm;
        }

        .menu-table th:nth-child(5),
        .menu-table td:nth-child(5) {
            width: 30mm;
        }

        .menu-table tr {
            page-break-inside: avoid;
        }

        .signature-box {
            display: grid;
            grid-template-columns: 1fr 1fr 1.5fr;
            gap: 10px;
            /* Reduced from 20px */
            margin-top: 10px;
            /* Reduced from 25px */
        }

        .signature-block {
            padding: 8px;
            /* Reduced from 15px */
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            position: relative;
            /* Added to contain absolute positioning */
        }

        .signature-header {
            font-weight: 600;
            color: #1a237e;
            margin-bottom: 8px;
            /* Reduced from 15px */
            font-size: 8pt;
        }

        .signature-line {
            border-bottom: 1px solid #1a237e;
            /* Thinner line */
            height: 20px;
            /* Reduced from 40px */
            margin: 5px 0;
            width: 100%;
            /* Explicit width for PDF consistency */
        }

        .signature-label {
            font-size: 7pt;
            color: #666;
            position: static;
            /* Changed from absolute for PDF reliability */
            margin-top: 2px;
            /* Small offset below line */
            line-height: 1;
            /* Tightens spacing */
            text-align: left;
        }

        .signature-date {
            margin-top: 10px;
            /* Reduced from 25px */
            display: flex;
            justify-content: space-between;
            font-size: 8pt;
        }

        .comment-block {
            padding: 8px;
            /* Reduced from 15px */
            border: 1px dashed #1a237e;
            border-radius: 4px;
            background: #f8fafc;
        }

        .comment-area {
            min-height: 40px;
            /* Reduced from 80px */
        }

        .comment-line {
            border-bottom: 1px solid #e0e0e0;
            margin: 5px 0;
            /* Reduced from 10px */
            height: 10px;
            /* Reduced from 15px */
        }

        ul {
            list-style: none;
            padding-left: 5px;
            /* Reduced from 10px */
            margin: 0;
        }

        ul li {
            margin-bottom: 1px;
            /* Reduced from 2px */
            font-size: 8pt;
        }

        ul li::before {
            content: "•";
            color: #1a237e;
            display: inline-block;
            width: 1em;
            margin-left: -1em;
        }

        .watermark {
            position: fixed;
            opacity: 0.1;
            font-size: 30pt;
            /* Reduced from 40pt */
            width: 100%;
            text-align: center;
            top: 50%;
            transform: rotate(-45deg);
            z-index: -1;
        }

        @media print {
            .signature-block {
                border-color: #ccc;
            }

            .comment-block {
                background: transparent;
                border-style: solid;
            }
        }


        /* Styling for the logo container */
        .logo-container {
            position: relative;
            margin-top:-8%;
            left: 0;
            padding: 20px;
            /* Adds spacing from the edge */
            text-align: center;
            /* Aligns text to the left */
            margin-right: 85%;
            height: 100vh;
            width: 150px;
        }

        /* Styling for the logo wrapper */
        .logo {

            color: black;
            /* Matches black text */
            line-height: 1.0;
            /* Slightly increased for better spacing */
        }

        /* Style for the "B" letter */
        .logo-letter {
            font-family: 'Brown Sugar', cursive; Placeholder for Brown Sugar
            display: inline-block;
            white-space: pre;
            Preserves text formatting font-family: monospace;
            /* Ensures alignment in pyramid shape */
            font-size: 72px;
            font-weight: bold;

        }

        /* Style for "BRICKSPOINT" */
        .logo-text {
            font-family: 'Lobster', cursive;
            /* Placeholder for Brown Sugar */
            font-size: 24px;
            font-weight: bold;

        }

        /* Style for "Boutique Aparthotel" */
        .logo-subtitle {
            font-family: 'Montserrat', sans-serif;
            /* Placeholder for Gotham */
            font-size: 16px;
            font-weight: normal;
            text-align: center
        }

        /* Style for "Asokoro" */
        .logo-location {
            font-family: 'Montserrat', sans-serif;
            /* Placeholder for Gotham */
            font-size: 12px;
            font-weight: normal;
        }


        @media print {
            .header {
                border-bottom-color: #ccc;
            }

            .logo {
                height: 60px;
                /* Adjust for print */
            }
        }
    </style>
</head>

<body>
    <div class="watermark">ORIGINAL</div>


    <!-- Logo Section -->
    <div class="logo-container">
        <div class="logo">
            <span class="logo-letter">B</span><br>
            <span class="logo-text">BRICKSPOINT</span><br>
            <span class="logo-subtitle">Boutique Aparthotel</span><br>
            <span class="logo-location">Asokoro</span>

        </div>

    </div>

    <div class="header">
        <h1>Function Sheet</h1>
        <div class="sub-header">
            Order ID: {{ $order->order_id }} |
            Created: {{ $order->preparation_date->format('M d, Y') }} |
            Last Updated: {{ $order->updated_at->format('M d, Y') }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">Customer Information</div>
        <table class="customer-table">
            <tr>
                <th>Organization</th>
                <td>{{ $order->customer->organization ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Name</th>
                <td>{{ $order->contact_person_name }}</td>
            </tr>
            <tr>
                <th>Department</th>
                <td>{{ $order->department ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Contact</th>
                <td>{{ $order->contact_person_phone }} | {{ $order->contact_person_email }}</td>
            </tr>
            <tr>
                <th>Secondary Contact</th>
                <td>{{ $order->contact_person_name_ii ?? 'N/A' }}<br>{{ $order->contact_person_phone_ii ?? '' }}
                    {{ $order->contact_person_email_ii ?? '' }}</td>
            </tr>
            <tr>
                <th>Referred By</th>
                <td>{{ $order->referred_by ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Event Schedule</div>
        @foreach ($order->eventDays as $day)
            <div style="page-break-inside: avoid;">
                <h3>{{ $day->event_date->format('l, F j, Y') }}</h3>
                <table class="event-table">
                    <tr>
                        <th>Description</th>
                        <td>{{ $day->event_description ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Details</th>
                        <td>{{ $day->event_type }} ({{ $day->event_status }})<br>{{ $day->room }} |
                            {{ $day->setup_style }}</td>
                    </tr>
                    <tr>
                        <th>Timing</th>
                        <td>{{ $day->start_time }} - {{ $day->end_time }}<br>{{ $day->duration_minutes }} minutes
                        </td>
                    </tr>
                    <tr>
                        <th>Guests</th>
                        <td>{{ $day->guest_count }} attendees</td>
                    </tr>
                </table>
            </div>
        @endforeach
    </div>

    <div class="section">
        <div class="section-title">Menu Details</div>
        @foreach ($order->eventDays as $day)
            <div style="margin-bottom: 5mm; page-break-inside: avoid;">
                <h3>{{ $day->event_date->format('M j, Y') }}</h3>
                <table class="menu-table">
                    <thead>
                        <tr>
                            <th>Meal Type</th>
                            <th>Menu Items</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($day->menuItems as $item)
                            <tr>
                                <td>{{ $item->meal_type }}</td>
                                <td>
                                    <ul>
                                        @foreach (json_decode($item->menu_items, true) as $menu)
                                            <li>{{ $menu }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>₦ {{ number_format($item->unit_price, 2) }}</td>
                                <td>₦ {{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>

    <div class="section">
        <div class="section-title">Authorization & Notes</div>
        <div class="signature-box">
            <div class="signature-block">
                <div class="signature-header">Authorized By:</div>
                <div class="signature-line"></div>
                <div class="signature-label">Client Signature</div>
                <div class="signature-date">
                    <span>Date:</span>
                    <span>___________________</span>
                </div>
            </div>
            <div class="signature-block">
                <div class="signature-header">Approved By:</div>
                <div class="signature-line"></div>
                <div class="signature-label">F&B Coordinator</div>
                <div class="signature-date">
                    <span>Date:</span>
                    <span>___________________</span>
                </div>
            </div>
            <div class="comment-block">
                <div>📝 <span style="font-weight: 600; color: #1a237e;">Special Notes</span></div>
                <div style="color: #666; font-size: 7pt; font-style: italic; margin-bottom: 5px;">(Additional
                    instructions)</div>
                <div class="comment-area">
                    <div class="comment-line"></div>
                    <div class="comment-line"></div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
