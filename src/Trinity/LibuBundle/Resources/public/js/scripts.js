jQuery(document).ready(function(){
    // This button will increment the value
    $('.qtyplus').click(function(e){
        // Stop acting like a button
        e.preventDefault();
        // Get the field name
        var fieldName = $(this).attr('field');
        var precio = $(this).val();
        // Get its current value
        var currentVal = parseInt($('input[id=venta_'+fieldName+']').val());
        var totalVal = parseFloat(($('#importe')).html());    
        // If is not undefined
        if (!isNaN(currentVal)) {
            // Increment
            $('input[id=venta_'+fieldName+']').val(currentVal + 1);
            $('#importe').html(totalVal + parseFloat(precio));

        } else {
            // Otherwise put a 0 there
            $('input[id=venta_'+fieldName+']').val(0);
        }
    });
    // This button will decrement the value till 0
    $(".qtyminus").click(function(e) {
        // Stop acting like a button
        e.preventDefault();
        // Get the field name
        fieldName = $(this).attr('field');
        // Get its current value
        var currentVal = parseInt($('input[id=venta_'+fieldName+']').val());
        var totalVal = parseFloat(($('#importe')).html());  
        var precio = {'libros3': '3', 'libros1':'1'};    
        
        // If it isn't undefined or its greater than 0
        if (!isNaN(currentVal) && currentVal > 0) {
            // Decrement one
            $('input[id=venta_'+fieldName+']').val(currentVal - 1);
            $('#importe').html(totalVal - parseFloat(precio[fieldName]));
          
        } else {
            // Otherwise put a 0 there
            $('input[id=venta_'+fieldName+']').val(0);
        }
    });

    $('.qtyprplus').click(function(e){
        // Stop acting like a button
        e.preventDefault();

        // Recibe el número de producto desde el button
        var clave = $(this).data('key');

        // elemento es el <span class='datos'>
        var elemento = $('#datos_' + clave);      

        // Recibe el precio desde el span  
        var precio = elemento.data('precio');

        // elemento.html('Precio: ' + precio) ;        // DEBUG
        // elemento.html('Clave: ' + clave) ;           // DEBUG

        // currentVal es el valor (integer) del input del elemento actual
        var currentVal = parseInt($('input[id=venta_product_'+clave+']').val());

        // totalVal es el valor (float) que aparece actualmente en el importe
        var totalVal = parseFloat(($('#importe')).html());    

        // Si no es indefinido
        if (!isNaN(currentVal)) {
            // Incrementa el valor del input en un entero
            $('input[id=venta_product_'+clave+']').val(currentVal + 1);
            // Incrementa el valor del importe según el precio
            $('#importe').html(totalVal + parseFloat(precio));

        } else {
            // En otro caso, se pone un cero en el input
            $('input[id=venta_product_'+clave+']').val(0);
        }
    });


    // This button will decrement the value till 0
    $(".qtyprminus").click(function(e) {
        // Stop acting like a button
        e.preventDefault();

        // Recibe el número de producto desde el button
        var clave = $(this).data('key');

        // elemento es el <span class='datos'>
        var elemento = $('#datos_' + clave);      

        // Recibe el precio desde el span  
        var precio = elemento.data('precio');

        // elemento.html('Precio: ' + precio) ;        // DEBUG
        // elemento.html('Clave: ' + clave) ;           // DEBUG

        // currentVal es el valor (integer) del input del elemento actual
        var currentVal = parseInt($('input[id=venta_product_'+clave+']').val());

        // totalVal es el valor (float) que aparece actualmente en el importe
        var totalVal = parseFloat(($('#importe')).html());    

        // Si no es indefinido
        if (!isNaN(currentVal)  && currentVal > 0) {
            // Decrementa el valor del input en un entero
            $('input[id=venta_product_'+clave+']').val(currentVal - 1);
            // Decrementa el valor del importe según el precio
            $('#importe').html(totalVal - parseFloat(precio));

        // elemento.html('CurrentVal: ' + currentVal) ;           // DEBUG


        } else {
            // En otro caso, se pone un cero en el input
            $('input[id=venta_product_'+clave+']').val(0);
        }    });
});