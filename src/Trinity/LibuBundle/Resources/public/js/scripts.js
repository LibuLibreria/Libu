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
        // Get the field name
        var clave = $(this).data('key');
        var elemento = $('#datos_' + clave);        

        var precio = elemento.data('precio');

        elemento.html('Resultado: ' + precio) ;        
        // Get its current value
        var currentVal = parseInt($('input[id=venta_product_'+clave+']').val());
        var totalVal = parseFloat(($('#importe')).html());    
        // If is not undefined
        if (!isNaN(currentVal)) {
            // Increment
            $('input[id=venta_'+clave+']').val(currentVal + 1);
            $('#importe').html(totalVal + parseFloat(precio));

        } else {
            // Otherwise put a 0 there
            $('input[id=venta_'+fieldName+']').val(0);
        }
    });
    // This button will decrement the value till 0
    $(".qtyprminus").click(function(e) {
        // Stop acting like a button
        e.preventDefault();
        // Get the field name
        fieldName = $(this).attr('data-key');
        // Get its current value
        var currentVal = parseInt($('input[id=venta_product_'+fieldName+']').val());
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
});