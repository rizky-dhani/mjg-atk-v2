<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: monospace; font-size: 13px; line-height: 1.6; }
        .header { background: #dc3545; color: white; padding: 15px; }
        .section { margin: 15px 0; padding: 15px; border: 1px solid #ddd; }
        .label { font-weight: bold; color: #333; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="header">
        <strong>ERROR LOG</strong> — {{ $timestamp }}
    </div>

    <div class="section">
        <span class="label">Exception:</span>
        <pre>{{ $exceptionClass }}</pre>
    </div>

    <div class="section">
        <span class="label">Message:</span>
        <pre>{{ $exceptionMessage }}</pre>
    </div>

    <div class="section">
        <span class="label">URL:</span>
        <pre>{{ $requestMethod }} {{ $requestUrl }}</pre>
    </div>

    <div class="section">
        <span class="label">IP:</span>
        <pre>{{ $clientIp }}</pre>
    </div>

    <div class="section">
        <span class="label">Stack Trace (first 20 frames):</span>
        <pre>{{ $stackTrace }}</pre>
    </div>
</body>
</html>
