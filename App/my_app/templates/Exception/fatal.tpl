<!DOCTYPE html>
<head>
<title>MVC - Exception {$title}</title>
<style type="text/css">
<!--
#content_div{
    display: block;font-family: monospace;padding: 9.5px;margin: 0 0 50px;font-size: 13px;line-height: 1.42857143;color: #333;word-break: break-all;word-wrap: break-word;background-color: #f5f5f5;border: 1px solid #ccc;border-radius: 4px;
    min-height: 200px;max-height: 400px;overflow: auto;
}

body{
    background-color: #f6f6f6; width: 100%;height:100%; margin: 0px; padding: 0px;
}

#header {
    width: 100%; min-height: 150px;background-color: #22395c;color: whitesmoke;
}
-->
</style>
</head>
<body>
<div id="header">
    <div style="padding: 20px;line-height: 30px;"><h1>FATAL ERROR</h1>
    Exception: <b>{$name}</b> {if isset($debug_info.class)}in Class: <b>{$debug_info.class}</b>{/if} {if isset($debug_info[1].function)} Function: <b>{$debug_info[1].function}</b>{/if} {if isset($debug_info.line)} Line: <b>{$debug_info.line}</b>{/if}<br />Message: <b>{$message}</b>
    </div>
</div>
<div style="margin: 50px;">
    <div id="content_div">
        <h2>Debug info</h2>
        <pre>
        {$debug_info|print_r:true}
        </pre>
    </div>
    <div id="content_div">
        <h2>Runtime content</h2>
        {$content}
    </div>
</div>  
</body>