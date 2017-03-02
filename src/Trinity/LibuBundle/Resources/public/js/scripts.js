jQuery(document).ready(function(){
    // Botón que incrementa el valor
    $('.qtyplus').click(function(e){
        // Stop acting like a button
        e.preventDefault();
        // Recibe el nombre del item: libros1 o libros3
        var fieldName = $(this).attr('field');

        // Recibe el precio a partir del value del botón
        var precio = $(this).val();

        // Recibe el valor actual del input
        var currentVal = parseInt($('input[id=venta_'+fieldName+']').val());

        // Recibe el valor actual en el importe
        var totalVal = parseFloat(($('#importe')).html());   

        // Si no está indefinido
        if (!isNaN(currentVal)) {
            // Incrementa
            if (precio == 3) {
                // Valores diferentes según el valor actual
                if (currentVal == 1) { precio = '2' };
                if (currentVal == 3) { precio = '2' };
                if (currentVal == 4) { precio = '0' };
                if (currentVal >= 5) { precio = '2' };
            }

            // Incrementamos en 1 el valor de currentVal en el input
            $('input[id=venta_'+fieldName+']').val(currentVal + 1);

            // Incrementamos en 'precio' el valor de totalVal en el importe
            var subimporte = (totalVal + parseFloat(precio)).toFixed(2);
            $('#importe').html(subimporte);

        } else {
            // En otro caso pone un cero en el input
            $('input[id=venta_'+fieldName+']').val(0);
        }
    });

    // Botón que decrementa el valor
    $('.qtyminus').click(function(e){
        // Stop acting like a button
        e.preventDefault();
        // Recibe el nombre del item: libros1 o libros3
        var fieldName = $(this).attr('field');

        // Recibe el precio a partir del value del botón
        var precio = $(this).val();

        // Recibe el valor actual del input
        var currentVal = parseInt($('input[id=venta_'+fieldName+']').val());

        // Recibe el valor actual en el importe
        var totalVal = parseFloat(($('#importe')).html());    

        // Si no está indefinido ni es cero o menor
        if (!isNaN(currentVal) && currentVal > 0) {
            // Decrementa
            if (precio == 3) {
                // Valores diferentes según el valor actual
                if (currentVal == 2) { precio = '2' };
                if (currentVal == 4) { precio = '2' };
                if (currentVal == 5) { precio = '0' };
                if (currentVal > 5) { precio = '2' };
            }

            // Decrementamos en 1 el valor de currentVal en el input
            $('input[id=venta_'+fieldName+']').val(currentVal - 1);

            // Decrementamos en 'precio' el valor de totalVal en el importe
            var subimporte = (totalVal - parseFloat(precio)).toFixed(2);
            $('#importe').html(subimporte);            

        } else {
            // En otro caso pone un cero en el input
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
            var subimporte = (totalVal + parseFloat(precio)).toFixed(2);
            $('#importe').html(subimporte);

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
            var subimporte = (totalVal - parseFloat(precio)).toFixed(2);
            $('#importe').html(subimporte);

        // elemento.html('CurrentVal: ' + currentVal) ;           // DEBUG


        } else {
            // En otro caso, se pone un cero en el input
            $('input[id=venta_product_'+clave+']').val(0);
        }    });
});

$(document).ready(function(){
$("#mytable #checkall").click(function () {
        if ($("#mytable #checkall").is(':checked')) {
            $("#mytable input[type=checkbox]").each(function () {
                $(this).prop("checked", true);
            });

        } else {
            $("#mytable input[type=checkbox]").each(function () {
                $(this).prop("checked", false);
            });
        }
    });
    
    $("[data-toggle=tooltip]").tooltip();
});
