/**
 * Class main API
 *
 * @class oApi
 * @constructor
 */
var oAPI = function( url, useMe  )
{
    this.url = url ;
    this.dataUrl = url;
    this.me = {};

    if( useMe !== false ) this.me.data = this.GET( 'API/user/me');

    /**
     * Checks if the user has a role
     *
     * @method isGranted
     * @param {String} role The role uid
     */
    this.me.isGranted = function(role)
    {
        for(var i =0; i < this.data.roles.length ; i++ )
        {
            if(this.data.roles[i] === role)
                return true;
        }

        return false;
    }

    /**
     * Return user attribute
     *
     * @method getAtt
     * @param {String} att The att key
     */
    this.me.getAtt = function (att)
    {
        return (  typeof this.data[att] == 'undefined' ) ? false : this.data[att];
    }
}

/**
 * Set base url used in HTTP operations
 *
 * @method setBaseUrl
 * @param {String} url The base url from project
 */
oAPI.prototype.setBaseUrl = function( url )
{
    this.url = url;
    this.dataUrl = url;
}

/**
 * Load template and populate
 *
 * @method render
 * @param {String} template The template name - Module.template
 * @param {Object} data The params used in populate tamplete
 * @param {String} append The selector of element append template
 * @return {String} The template
 */
oAPI.prototype.render = function ( template , data , append )
{
    try
    {
        if(append)
            return $(append).append( new EJS({url: this.url + 'API/ejs/' + template  }).render( { data: data } ) );
        else
            return new EJS({url: this.url + 'API/ejs/' + template }).render( { data: data } );
    }
    catch(e)
    {
        Exception.logException( 'API.render'  , e ,  arguments )
        return false;
    }
}

/**
 * Dynamic load javaScript files
 *
 * @method getJS
 * @param {String} url The script name - Module.script
 * @param {Function} callback The function executed after load
 */
oAPI.prototype.getJS = function ( url, callback )
{
    return $.getScript( this.url + 'API/js/' + url, callback );
}

/**
 * Dynamic load javaScript plugins
 *
 * @method getPlugin
 * @param {String} url The script name - Module.script
 * @param {Function} callback The function executed after load
 */
oAPI.prototype.getPlugin = function ( url, callback )
{
    return $.getScript( this.url + 'bundles/SocialNetworkapi/plugins/' + url, callback );
}

/**
 * Abstraction of search in Rest
 * The search create new resource
 *
 * @method search
 * @param {String} url The uri
 * @param {Object} data The parameters on resource
 * @param {Function} callback The function executed after load
 * @return {Object} The response object
 */
oAPI.prototype.search = function ( url , data , callback )
{
    var _this = this;

    if( callback )
    {
        _this.AJAX( 'POST' , this.dataUrl + url , data  , function ( result, tStatus, jqXHR )
        {
            var result = _this.AJAX( 'GET', _this.dataUrl + jqXHR.getResponseHeader('Location') );
            callback(result);
        });
    }else{
        var search = _this.AJAX( 'POST' , this.dataUrl + url , data  );
        return _this.AJAX( 'GET', _this.dataUrl + search.Location );
    }

}

/**
 * Execute GET in HTTP protocol
 *
 * @method GET
 * @param {String} url The uri
 * @param {Function} success The function executed after load if success operation
 * @param {Function} error The function executed after load if error operation
 * @return {Object} The response object
 */
oAPI.prototype.GET = function ( url , success , error )
{
    return this.AJAX( 'GET', this.dataUrl + url, '', success, error );
}

/**
 * Execute PUT in HTTP protocol
 *
 * @method PUT
 * @param {String} url The uri
 * @param {Object} data The parameters of resource
 * @param {Function} success The function executed after load if success operation
 * @param {Function} error The function executed after load if error operation
 * @return {Object} The response object
 */
oAPI.prototype.PUT = function ( url , data , success , error )
{
    return this.AJAX( 'PUT', this.dataUrl + url , data , success , error );
}

/**
 * Execute DELETE in HTTP protocol
 *
 * @method DELETE
 * @param {String} url The uri
 * @param {Function} success The function executed after load if success operation
 * @param {Function} error The function executed after load if error operation
 * @return {Object} The response object
 */
oAPI.prototype.DELETE = function ( url , success , error )
{
    return this.AJAX( 'DELETE', this.dataUrl + url , '' , success , error );
}

/**
 * Execute POST in HTTP protocol
 *
 * @method POST
 * @param {String} url The uri
 * @param {Object} data The parameters of resource
 * @param {Function} success The function executed after load if success operation
 * @param {Function} error The function executed after load if error operation
 * @return {Object} The response object
 */
oAPI.prototype.POST = function (url , data , success , error)
{
    return this.AJAX( 'POST', this.dataUrl + url , data , success , error );
}

/**
 * Abstration of assync operations
 *
 * @method AJAX
 * @param {String} type The type HTTP operation
 * @param {String} uri The uri of resource
 * @param {Object} data The parameters of resource
 * @param {Function} successCall The function executed after load if success operation
 * @param {Function} errorCall The function executed after load if error operation
 * @return {Object} The response object
 */
oAPI.prototype.AJAX = function(type, uri, data, successCall, errorCall){

    var returns = false, fired = false;

    var settings = {
        type: type,
        data: data,
        async: (!!successCall ? true : false),
        url: uri,
        error: function(result, tStatus, jqXHR){

            var error = result.responseText ? $.parseJSON( result.responseText ) : 'Internal server error';
            Exception.log( 'API.'+type, error.error );

            if( errorCall ){
                fired = true;
                returns = errorCall( error );
            }else if( successCall ){
                fired = true;
                returns = successCall( error );
            }else

                returns = error;
        },
        success: function(result, tStatus, jqXHR){
            if( successCall ){
                fired = true;
                returns = successCall( result, tStatus, jqXHR );
            }else{
                returns = result;
            }
        },
        complete: function(jqXHR, tStatus){

            if( !fired && successCall ) returns = successCall(false, tStatus, jqXHR);

        }
    }

    $.ajax( settings );
    return returns;
}

/**
 * Check error in response data
 *
 * @method hasError
 * @param {Object} data The response object
 * @return {Boolean} Returns false on success
 */
oAPI.prototype.hasError = function( data ){

    if(!$.isEmptyObject( data ) && data.error ) return true;

    return false;
}

/**
 * Load data from form
 *
 * @method form
 * @param {String} target The selector of form
 * @param {String} fileInputs The selector of inputs
 * @return The object from in form
 */
oAPI.prototype.form = function ( target, fileInputs ){

    var result = {}, $this = $(target), inputArray = $this.serializeArray();
    var arrayName = /^([A-z0-9-_]+)\[\]$/;

    if( !$this.is( "form" ) )
        $this = $this.parents( "form" );

    if( fileInputs )
        fileInputs.each( function( i, el ){

            inputArray[ inputArray.length ] = { name: $(this).prop("name"), value: FILE + i };

        });

    $.each( inputArray, function( i, el ){

        if( newName = arrayName.exec( el.name ) )
            el.name = newName[1];
        else if( !result[ el.name ] )
            return( result[ el.name ] = el.value );

        result[ el.name ] = result[ el.name ] || [];

        if( $.type(result[ el.name ]) !== "array" )
            result[ el.name ] = [ result[ el.name ] ];

        result[ el.name ].push( el.value );
    });


    return result;
}


/**
 * Extend the jquery getScrip and helps with browser debugging.
 *
 * @method getScript
 * @param {String} url The selector of form
 * @param {Function} callback The function executed after load
 */
$.extend({
    getScript: function(url, callback) {
        var head	= document.getElementsByTagName("head")[0];
        var script	= document.createElement("script");
        var done 	= false; // Handle Script loading

        script.src	= url;
        script.charset = 'UTF-8';
        script.onload = script.onreadystatechange = function() { // Attach handlers for all browsers
            if ( !done && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete") ) {
                done = true;
                if (callback) { callback(); }
                script.onload = script.onreadystatechange = null; // Handle memory leak in IE
            }
        };

        head.appendChild(script);
        return undefined; // We handle everything using the script element injection
    }
});
