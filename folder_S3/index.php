<html>
    <head>
        <title>Subir archivo</title>
    </head>
    <body>
        <!-- El tipo de codificación de datos, enctype, DEBE especificarse como sigue -->
<form enctype="multipart/form-data" action="controlador2.php" method="post">
    <!-- MAX_FILE_SIZE debe preceder al campo de entrada del fichero -->
    <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
    <!-- El nombre del elemento de entrada determina el nombre en el array $_FILES -->
    Enviar este fichero: <input name="archivo_" type="file" />
    <input type="hidden" name="funcion" value="1"/>
    <input type="hidden" name="folder" value="Folder/"/>
    <input type="submit" value="Enviar Archivo" />
</form>
        
        <form method="POST" action="controlador1.php" method="post">
            <input type="hidden" name="funcion" value="2"/>
            <input type="submit" value="Listar archivos" />
        </form>
    </body>
    
</html>