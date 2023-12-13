jQuery( function($) {
    $( 'input[name="ip_address_format"]' ).on( 'click', function() {
        if ( 'ip_address_format_custom_radio' !== $(this).attr( 'id' ) )
            $( 'input[name="ip_address_format_custom"]' ).val( $( this ).val() ).closest( 'fieldset' ).find( '.example' ).text( $( this ).parent( 'label' ).children( '.format-i18n' ).text() );
    });

    $( 'input[name="ip_address_format_custom"]' ).on( 'input', function() {
        $( '#ip_address_format_custom_radio' ).prop( 'checked', true );
    } );
} );