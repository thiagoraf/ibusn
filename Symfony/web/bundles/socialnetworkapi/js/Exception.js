/**
 * Class exceptions log in client debug
 *
 * @class oException
 * @constructor
 */
oException = function (){}

/**
 * Create error log
 *
 * @method error
 * @param {String} scope The function name
 */
oException.prototype.error = function ( scope )
{
    //TODO: Tratar erros
    console.log(' Erro: Function  ' + scope);
}

/**
 * Create info log
 *
 * @method log
 * @param {String} scope The function name
 * @param {String} msg The custom message
 */
oException.prototype.log = function ( scope , msg )
{
    //TODO: Tratar erros
    console.log(' LOG: ' + msg +  ' | Function  ' + scope);
}

/**
 * Create exception log
 *
 * @method log
 * @param {String} scope The function name
 * @param {Object} exception The exception object
 */
oException.prototype.logException = function ( scope , exception)
{
    //TODO: Tratar erros
    console.log(' Erro : '+ exception.message +' | Function  ' + scope );
}

var Exception = new oException();