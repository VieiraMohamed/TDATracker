<?php
session_start();
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ./inicio_sesion.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pago Usuario Premium</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f4f7fc;
      font-family: Arial, sans-serif;
      height: 100vh;
    }
    .container {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      height: 100%;
      padding: 30px;
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 800px;
      margin: 0 auto;
    }
    h3 {
      font-size: 24px;
      margin-bottom: 20px;
      color: #333;
    }
    .subscription-options {
      display: flex;
      justify-content: center;
      gap: 30px;
      margin-bottom: 20px;
    }
    .subscription-option {
      padding: 20px;
      background-color: #f9f9f9;
      border-radius: 10px;
      border: 1px solid #ddd;
      text-align: center;
      width: 250px;
      cursor: pointer;
      transition: transform 0.3s, box-shadow 0.3s;
    }
    .subscription-option:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .subscription-option.selected {
      border: 2px solid #28a745;
      background-color: #e9f7e6;
    }
    .subscription-option h5 {
      font-size: 20px;
      margin-bottom: 10px;
    }
    .subscription-option p {
      font-size: 14px;
      color: #555;
    }
    .price {
      font-size: 18px;
      font-weight: bold;
      margin: 10px 0;
    }
    #paypal-button-container {
      margin-top: 30px;
      display: flex;
      justify-content: center;
      width: 100%;
    }
    @media (max-width: 600px) {
      .subscription-options {
        flex-direction: column;
        gap: 20px;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <h3>Elige tu Plan Premium como Usuario</h3>

    <div class="subscription-options">
      <!-- Plan Mensual -->
      <div id="planMensual" class="subscription-option">
        <h5>Plan Mensual</h5>
        <p>Disfruta de acceso completo durante un mes</p>
        <ul>
          <li>Videollamadas</li>
          <li>Minijuegos</li>
          <li>Chat IA</li>
        </ul>
        <div class="price">3€ / mes</div>
      </div>

      <!-- Plan Anual -->
      <div id="planAnual" class="subscription-option">
        <h5>Plan Anual</h5>
        <p>Disfruta de acceso completo durante todo el año</p>
        <ul>
          <li>Videollamadas</li>
          <li>Minijuegos</li>
          <li>Chat IA</li>
        </ul>
        <div class="price">30€ / año</div>
      </div>
    </div>

    <div id="paypal-button-container"></div>
    <div id="resultadoPago" class="mt-4 text-center"></div>
  </div>

  <?php
    // Lee el client_id de PayPal desde el archivo de texto
    $paypalClientId = file_get_contents('paypal_client_id.txt');
  ?>

  <script src="https://www.sandbox.paypal.com/sdk/js?client-id=<?php echo $paypalClientId; ?>&currency=EUR"></script>

  <script>
    let subscriptionType = 'monthly';

    // Selección del Plan
    const planMensual = document.getElementById('planMensual');
    const planAnual = document.getElementById('planAnual');

    planMensual.addEventListener('click', () => {
      subscriptionType = 'monthly';
      setActivePlan(planMensual, planAnual);
      renderPaypalButton();
    });

    planAnual.addEventListener('click', () => {
      subscriptionType = 'annual';
      setActivePlan(planAnual, planMensual);
      renderPaypalButton();
    });

    function setActivePlan(active, inactive) {
      active.classList.add('selected');
      inactive.classList.remove('selected');
    }

    function renderPaypalButton() {
      document.getElementById('paypal-button-container').innerHTML = '';
      const amount = subscriptionType === 'monthly' ? '3.00' : '30.00';

      paypal.Buttons({
        createOrder: function(data, actions) {
          return actions.order.create({
            purchase_units: [{
              amount: { value: amount }
            }]
          });
        },
        onApprove: function(data, actions) {
          return actions.order.capture().then(function(details) {
            const plan = subscriptionType;
            fetch('./procesar_pago.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ plan })
            })
            .then(res => res.json())
            .then(data => {
              if (data.exito) {
                document.getElementById("resultadoPago").innerHTML = `
                  <div class="alert alert-success">✅ ¡Gracias! Tu plan premium está activo hasta ${data.expiracion}.</div>
                  <a href="../dashboard.php" class="btn btn-primary">Volver al Dashboard</a>`;
              } else {
                document.getElementById("resultadoPago").innerHTML = `<div class="alert alert-danger">❌ Error al actualizar el plan.</div>`;
              }
            });
          });
        }
      }).render('#paypal-button-container');
    }

    renderPaypalButton();
  </script>

</body>
</html>
