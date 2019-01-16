(function($){
    $('button.shipping_coordinadora_update_cities').click(function(e){
        e.preventDefault();
        $.ajax({
            method: 'GET',
            url: ajaxurl,
            data: {action: 'shipping_coordinadora_wc_cswc'},
            beforeSend: function(){
                swal({
                    title: 'Actualizando',
                    onOpen: () => {
                        swal.showLoading()
                    },
                    allowOutsideClick: false
                });
            },
            success: function(){
                swal({
                    title: 'Se ha actualizado exitosamente',
                    text: 'redireccionando a configuraciones...',
                    type: 'success',
                    showConfirmButton: false
                });
                window.location.replace(shippingCoordinadora.urlConfig);
            }
        });
    });
})(jQuery);