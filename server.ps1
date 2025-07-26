# Simple HTTP Server in PowerShell
$server = New-Object System.Net.HttpListener
$server.Prefixes.Add('http://localhost:8000/')
$server.Start()

Write-Host 'Server started at http://localhost:8000/'
Write-Host 'Press Ctrl+C to stop the server'

try {
    while ($server.IsListening) {
        $context = $server.GetContext()
        $request = $context.Request
        $response = $context.Response
        $localPath = $request.Url.LocalPath
        
        if ($localPath -eq '/') {
            $localPath = '/index.html'
        }
        
        $filePath = Join-Path (Get-Location) $localPath.TrimStart('/')
        
        if (Test-Path $filePath -PathType Leaf) {
            $content = [System.IO.File]::ReadAllBytes($filePath)
            $response.ContentLength64 = $content.Length
            $response.OutputStream.Write($content, 0, $content.Length)
        } else {
            $response.StatusCode = 404
            $notFoundMessage = "File not found: $localPath"
            $notFoundBytes = [System.Text.Encoding]::UTF8.GetBytes($notFoundMessage)
            $response.ContentLength64 = $notFoundBytes.Length
            $response.OutputStream.Write($notFoundBytes, 0, $notFoundBytes.Length)
        }
        
        $response.Close()
    }
} finally {
    $server.Stop()
}