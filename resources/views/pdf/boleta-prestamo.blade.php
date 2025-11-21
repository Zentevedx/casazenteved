<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Papeleta de Empeño</title>
  <style>
    body {
  font-family: "Liberation Sans", sans-serif;
      font-size: 11px;
      margin: 10px 20px;
    }
    
  


    .logo {
      width: 65px;
      height: auto;
      margin: 0px;
      
    }

    .row {
      width: 100%;
      display: block;
      clear: both;
      margin-bottom: 10px;
    }

    .col-6 {
      width: 70%;
      float: left;
      box-sizing: border-box;
    }

    .col-3 {
      width: 48%;
      float: left;
      box-sizing: border-box;
    }
    .col-2 {
      width: 20%;
      float: right;
      text-align: right;
    }

    .empresa-info {
      font-size: 10px;
      line-height: 1.3;
      margin-left: 15px;
    }
    .empresa-header {
  display: flex;
  align-items: center;
  gap: 10px;
}

    

    .codigo-label {
      font-size: 10px;
    }

    .codigo {
      font-size: 25px;
      color: #0b4f64ff;
      font-weight: bold;
    }

    .rounded-card {
      border-radius: 10px;
      background-color: #ffffffff;
      padding: 5px;
      border: 1px solid #0b4f64ff;
      margin-bottom: 5px;
      clear: both;
    }

    .rounded-table {
      border: 1px solid #1f1f1f;
      width: 100%;
      border-collapse: collapse;
      overflow: hidden;
    }

    .rounded-table th, .rounded-table td {
      border: 1px solid #1f1f1fff;
      padding: 3px;
      font-size: 10px;
    }

    .rounded-table th {
      background-color: #ffffffff;
    }

    .nota {
      font-size: 9px;
      font-style: italic;
    }

    .firma-linea {
      border-top: 1px solid #000;
      width: 200px;
      text-align: center;
      margin-top: 35px;
      padding-top: 4px;
    }

    .text-center {
      text-align: center;
    }

    .font-bold {
      font-weight: bold;
    }
.col-3 {
  width: 15%;
  float: left;
  box-sizing: border-box;
}

.col-6 {
  width: 65%;
  float: left;
  box-sizing: border-box;
}

.text-right {
  text-align: right;
}

.clearfix::after {
  content: "";
  display: table;
  clear: both;
}

    .clearfix::after {
      content: "";
      display: table;
      clear: both;
    }
  </style>
</head>
<body>

 <!-- Encabezado con tres columnas -->
<div class="row clearfix">
  <div class="col-3">
    <img src="{{ public_path('logo2.jpg') }}" class="logo">
  </div>
  <div class="col-6 empresa-info">
    <strong>CASA DE EMPEÑOS</strong><br>
    Av. Panamericana / Av. Martires de la Democracia<br>
    Whatsapp: 60763882<br>
    NIT: 8420256011
  </div>
  <div class="col-3 text-right">
    <div class="codigo-label">C Ó D I G O</div>
    <div class="codigo">{{ $prestamo->codigo }}</div>
  </div>
</div>


  <!-- Título -->
  <h4 class="text-center font-bold">PAPELETA DE EMPEÑO</h4>

  <!-- Datos del cliente -->
  <div class="rounded-card">
    <div class="row">
      <div class="col-6"><strong>Titular:</strong> {{ $prestamo->cliente->nombre }}</div>
      <div class="col-6"><strong>CI:</strong> {{ $prestamo->cliente->ci }}</div>
    </div>
    <div class="row">
      <div class="col-6"><strong>Fecha de préstamo:</strong> {{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('d/m/Y') }}</div>
      <div class="col-6"><strong>Vencimiento:</strong> {{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->addDays(30)->format('d/m/Y') }}</div>
    </div>
    <div class="row">
      <div class="col-6"><strong>Monto prestado:</strong> Bs {{ number_format($prestamo->monto, 2, ',', '.') }}</div>
      <div class="col-6"><strong>Multa por retraso:</strong> Bs {{ number_format($prestamo->multa_por_retraso ?? 0, 2, ',', '.') }}</div>
    </div>
  </div>

  <!-- Tabla de artículos -->
  <h5 class="mb-3">Artículos prendados</h5>
  <table class="rounded-table">
    <thead>
      <tr>
        <th style="width: 50px;">Nro</th>
        <th style="width: 200px;">Artículo</th>
        <th>Descripción</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($prestamo->articulos as $i => $articulo)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td>{{ $articulo->nombre_articulo }}</td>
          <td>{{ $articulo->descripcion }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <!-- Nota -->
  <p class="nota">
    El interés incluye los costos por almacenamiento y seguridad del artículo empeñado.
    En caso de retraso, se aplicará una multa diaria a partir del día siguiente al vencimiento establecido.
  </p>

  <!-- Firmas -->
  <div class="row clearfix" style="margin-top: 50px;">
    <div class="col-6 text-center">
      <div class="firma-linea">Firma del Cliente</div>
    </div>
    <div class="col-6 text-center">
      <div class="firma-linea">Firma del Encargado</div>
    </div>
  </div>

</body>
</html>
