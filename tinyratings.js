jQuery(function($){
  var tinyratings_fingerprint = false;
  new Fingerprint2().get(function(result, components){
    tinyratings_fingerprint = result;
    jQuery('.tinyratings-container').each(function(){
      var container = jQuery( this );
      var obj = container.data('object-id');
      var obj_type = container.data('object-type');
      var obj_subtype = container.data('object-subtype');
      var style = container.data('style');
      var uri = tinyratings_data.api_uri+ '/' + obj_type + '/' + obj;
      jQuery.get(
        uri,
        {
          'style'  : style,
          'fingerprint' : tinyratings_fingerprint,
          '_wpnonce' : tinyratings_data.api_nonce,
        }
      ).always( function( response ) {
        jQuery( container ).trigger( 'tinyratings.response', [ response, container ] );
      });
    });
    jQuery('.tinyratings-button').click(function(){
      var button = jQuery(this);
      if ( ! button.parents('.tinyratings-container').hasClass('tinyratings-active') ) {
        return false;
      }
      if ( button.parents('.tinyratings-container').hasClass('tinyratings-processing') ) {
        return false;
      }
      button.parents('.tinyratings-container').addClass('tinyratings-processing');
      var obj = button.parents('.tinyratings-container').data('object-id');
      var obj_type = button.parents('.tinyratings-container').data('object-type');
      var obj_subtype = button.parents('.tinyratings-container').data('object-subtype');
      var style = button.parents('.tinyratings-container').data('style');
      var rating = button.data('rating');
      // alert( tinyratings_data.api_uri );
      var uri = tinyratings_data.api_uri+ '/' + obj_type + '/' + obj;
      // uri += '?rating=' + rating;
      jQuery.post(
        uri,
        {
          'rating' : rating,
          'style'  : style,
          'subtype'  : obj_subtype,
          'fingerprint' : tinyratings_fingerprint,
          '_wpnonce' : tinyratings_data.api_nonce,
        }
      ).always( function( response ) {
        button.parents('.tinyratings-container').removeClass('tinyratings-processing');
        if ( 'undefined' !== typeof( response.status ) ) {
          return false;
        }
        jQuery( button.parents('.tinyratings-container') ).trigger( 'tinyratings.response', [ response, button.parents('.tinyratings-container'), button ] );
      });
    })
  });
  jQuery('.tinyratings-button').hover(function(){
    jQuery( this ).addClass( 'tinyratings-hover' );
  },function(){
    jQuery( this ).removeClass( 'tinyratings-hover' );
  })
})

jQuery(function($){
  jQuery('.tinyratings-style-like').bind( 'tinyratings.response', function( e, response, container ){
    container.find('.tinyratings-result').html( response.result );
    container.find('.tinyratings-button').removeClass('tinyratings-active');
    if ( null !== response.current ) {
      container.find('.tinyratings-button[data-rating="' + response.current.rating_value + '"]').addClass('tinyratings-active');
    }
  });
})
jQuery(function($){
  jQuery('.tinyratings-style-likedislike').bind( 'tinyratings.response', function( e, response, container ){
    container.find('.tinyratings-result').html( response.result );
    container.find('.tinyratings-button').removeClass('tinyratings-active');
    if ( null !== response.current ) {
      container.find('.tinyratings-button[data-rating="' + response.current.rating_value + '"]').addClass('tinyratings-active');
    }
  });
})
jQuery(function($){
  jQuery('.tinyratings-style-updown').bind( 'tinyratings.response', function( e, response, container ){
    container.find('.tinyratings-result').html( response.result );
    container.find('.tinyratings-button').removeClass('tinyratings-active');
    if ( null !== response.current ) {
      container.find('.tinyratings-button[data-rating="' + response.current.rating_value + '"]').addClass('tinyratings-active');
    }
  });
})

jQuery(function($){
  jQuery('.tinyratings-style-stars').bind( 'tinyratings.response', function( e, response, container ){
    var result = response.current.rating_value;
    result = Math.round( result * 2 ) / 2;
    container.find('.tinyratings-button span').removeClass('dashicons-star-filled');
    container.find('.tinyratings-button span').removeClass('dashicons-star-half');
    container.find('.tinyratings-button span').removeClass('dashicons-star-empty');
    for ( var i = 0; i <= 5; ++i ) {
      if ( 1 <= result - i ) {
        container.find('.tinyratings-button[data-rating="' + ( i + 1 ) + '"] span').addClass('dashicons-star-filled');
      } else if ( 0.5 == result - i ) {
        container.find('.tinyratings-button[data-rating="' + ( i + 1 ) + '"] span').addClass('dashicons-star-half');
      } else {
        container.find('.tinyratings-button[data-rating="' + ( i + 1 ) + '"] span').addClass('dashicons-star-empty');
      }
    }
    container.find('.tinyratings-result').html( response.result );
    container.find('.tinyratings-button').removeClass('tinyratings-active');
    if ( null !== response.current ) {
      container.find('.tinyratings-button[data-rating="' + response.current.rating_value + '"]').addClass('tinyratings-active');
    }

  });
  jQuery('.tinyratings-style-stars.tinyratings-active .tinyratings-button').hover(function(){
    jQuery( this ).prevAll().addClass( 'tinyratings-hover' );
  },function(){
    jQuery( this ).prevAll().removeClass( 'tinyratings-hover' );
  })
})
