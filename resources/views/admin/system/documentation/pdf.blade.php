<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            font-size: 11pt;
        }
        h1, h2, h3, h4 {
            color: #1a56db;
            margin-top: 1.5em;
            margin-bottom: 0.5em;
        }
        h1 {
            font-size: 24pt;
            border-bottom: 2px solid #1a56db;
            padding-bottom: 10px;
            text-align: center;
        }
        h2 {
            font-size: 18pt;
            border-left: 5px solid #1a56db;
            padding-left: 10px;
        }
        h3 {
            font-size: 14pt;
        }
        p {
            margin-bottom: 1em;
        }
        code {
            background-color: #f4f4f4;
            padding: 2px 4px;
            border-radius: 4px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 10pt;
        }
        pre {
            background-color: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
            overflow: hidden;
            margin-bottom: 1.5em;
        }
        pre code {
            background-color: transparent;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5em;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f9fafb;
            color: #111;
        }
        .footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 50px;
            font-size: 9pt;
            color: #777;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .header {
            text-align: right;
            font-size: 9pt;
            color: #aaa;
            margin-bottom: 20px;
        }
        blockquote {
            background: #f9f9f9;
            border-left: 10px solid #ccc;
            margin: 1.5em 10px;
            padding: 0.5em 10px;
            quotes: "\201C""\201D""\2018""\2019";
        }
    </style>
</head>
<body>
    <div class="header">
        Dicetak pada: {{ $date }}
    </div>

    <div class="content">
        {!! $content !!}
    </div>

    <div class="footer">
        © {{ date('Y') }} E-Sertifikat BPMP Kalimantan Timur - Dokumen Internal
    </div>
</body>
</html>
