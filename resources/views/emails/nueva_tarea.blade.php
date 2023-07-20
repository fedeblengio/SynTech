<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Schedule changed</title>


    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans&display=swap');

        body {
            background-color: #EDF2F7;
            font-family: 'Inter', sans-serif;
        }

        a {
            text-decoration: none;
        }

        .central_div {
            margin: 2rem auto auto auto;
            max-width: 500px;
            height: auto;
            background-color: white;
            border-radius: 2px 4px 2px;
            padding: 2rem;
        }

        .img_logo {
            margin: auto;
            display: flex;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .img_logo img {
            width: 200px;
        }

        .email_body {
            font-weight: 400;
            color: #718096;
        }

        h4 {
            font-weight: bold;
            font-size: 18px;
            color: #26A74E;
        }

        p {
            font-size: 16px;
            font-weight: 400;
            color: #718096;
        }

        span {
            font-size: 16px;
            font-weight: 450;
            color: #718096;
        }

        .container {
            width: 90%;
            margin-left: auto;
        }

        .btn-primary {
            color: white !important;
            background-color: #26A74E;
            border-color: #26A74E;
        }

        .btn-primary:hover {
            color: white !important;
            background-color: #26A74E;
            border-color: #26A74E;
        }

        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            border: 1px solid transparent;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: .25rem;
        }
    </style>
</head>
<body style="background-color: #EDF2F7;">
<div class="img_logo mt-2">
    <img style="margin-top:20px;width:200px;display:block;margin-left:auto;margin-right:auto"
         src="https://i.imgur.com/91w0Iwe.png"
         alt="LMS LOGO">
</div>
<div class="central_div ">
    <p>Una nueva tarea ha sido registrada,</p>
    <p>Se te informa que el profesor {{ $details['nombreUsuario']}} ha creado una nueva tarea en la materia de {{ $details['nombreMateria']}}.</p>
    <p>Recuerda que esta tarea la podras visualizar en el listado de tareas del grupo {{ $details['grupo'] }} </p>
 
    <br>
    <p style="padding-bottom: 2rem;border-bottom: 0.4px lightgrey solid">Saludos, <br> Equipo LMS</p>
    <p style="font-size: 12px;color: #718096;">Este correo es generado automaticamente, por favor no responder.</p>
</div>

</body>

</html>



