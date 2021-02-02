/**
 * All of the code for your admin-facing JavaScript source
 * should reside in this file.
 *
 * Note: It has been assumed you will write jQuery code here, so the
 * $ function reference has been prepared for usage within the scope
 * of this function.
 *
 * This enables you to define handlers, for when the DOM is ready:
 *
 * $(function() {
 *
 * });
 *
 * When the window is loaded:
 *
 * $( window ).load(function() {
 *
 * });
 *
 * ...and/or other possibilities.
 *
 * Ideally, it is not considered best practise to attach more than a
 * single DOM-ready or window-load handler for a particular page.
 * Although scripts in the WordPress core, Plugins and Themes may be
 * practising this, we should strive to set a better example in our own work.
 */
	jQuery( document ).ready(
		function($) {
			$( "#json_filesnames" ).change(
				function(){
					var filename = $( this ).val();

					jQuery.ajax(
						{
							type:'POST',
							url:frontendajax.ajaxurl,
							data:{
								action:'brand_action',
								filename:filename,
								nonce:frontendajax.nonce,
							},
							success:function(data){
								$( "#showdata" ).html( data );
							}
						}
					)
				}
			)
			$( document ).on(
				"click",
				".import_product",
				function(){
					var item_id       = $( this ).attr( "name" );
					var selected_file = $( "#json_filesnames" ).val();

					jQuery.ajax(
						{
							type:'POST',
							url:frontendajax.ajaxurl,
							data:{
								action:'product_import',
								filename:selected_file,
								item_id:item_id,
								nonce:frontendajax.nonce,
							},
							success:function(mess){
								console.log( mess );

								alert( "product imported sucessfully" );
							}
						}
					)
				}
			)

			$( document ).on(
				"click",
				"#doaction",
				function(){
					var bulkoption      = $( "#bulk-action-selector-top" ).val();
					var selected_file   = $( "#json_filesnames" ).val();
					var bulk_checkboxes = [];
					$( ':checkbox:checked' ).each(
						function(i){
							bulk_checkboxes[i] = $( this ).val();
						}
					);
					console.log( bulk_checkboxes );

					jQuery.ajax(
						{
							type:'POST',
							url:frontendajax.ajaxurl,
							data:{
								action:'bulk_import',
								bulkoption:bulkoption,
								selected_file:selected_file,
								bulkcheckboxes:bulk_checkboxes,
							},
							success:function(responce){
								console.log( responce );
								$( "#showdata" ).html( responce );
							}
						}
					)
				}
			)
			$( "#json_order_file" ).change(
				function(){
					var filename = $( this ).val();
					var product_filename   = $( "#json_filesnames" ).val();
					jQuery.ajax(
						{
							type:'POST',
							url:frontendajax.ajaxurl,
							data:{
								action:'order_import',
								order_filename:filename,
								product_filename:product_filename,
								nonce:frontendajax.nonce,
							},
							success:function(data){
								$( "#showdata" ).html( data );
							}
						}
					)
				}
			)

			
			
		}
	);
