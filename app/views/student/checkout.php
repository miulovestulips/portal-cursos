<h1>Finalizar compra</h1>
<p>Estás a un paso de comenzar <strong><?= htmlspecialchars($curso['titulo']) ?></strong></p>

<div class="checkout-resumen">
    <h3>Resumen del pedido:</h3>
    <p>Curso: <?= htmlspecialchars($curso['titulo']) ?></p>
    <p>Instructor: <?= htmlspecialchars($curso['instructor']) ?></p>
    <h2>Total a pagar: <?= Formato::moneda($curso['costo']) ?></h2>
</div>

<form method="post" action="/checkout/<?= $curso['id'] ?>/pagar" class="form-checkout">
    <h3>Selecciona tu método de pago:</h3>
    
    <label>
        <input type="radio" name="metodo_pago" value="Tarjeta de Crédito" required>
        Tarjeta de Crédito
    </label>
    
    <label>
        <input type="radio" name="metodo_pago" value="PayPal">
        PayPal
    </label>
    
    <label>
        <input type="radio" name="metodo_pago" value="Transferencia">
        Transferencia Bancaria
    </label>

    <button type="submit" class="btn btn-primary">Pagar y Comenzar Curso</button>
</form>