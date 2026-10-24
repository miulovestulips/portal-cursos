// public/js/api.js
// Cliente de la API propia con fetch y promesas.
// - No recarga la página.
// - Convierte cualquier respuesta de error (4xx/5xx o falla de red) en un
//   ApiError con un mensaje legible, para mostrarlo al usuario.
(function (global) {
  'use strict';

  var BASE = global.API_BASE || '/api';

  function ApiError(mensaje, status, detalles) {
    this.name = 'ApiError';
    this.message = mensaje;
    this.status = status;
    this.detalles = detalles || {};
  }
  ApiError.prototype = Object.create(Error.prototype);

  function request(metodo, ruta, cuerpo) {
    var opciones = {
      method: metodo,
      credentials: 'same-origin',
      headers: { Accept: 'application/json' }
    };
    if (cuerpo !== undefined) {
      opciones.headers['Content-Type'] = 'application/json';
      opciones.body = JSON.stringify(cuerpo);
    }

    return fetch(BASE + ruta, opciones)
      .catch(function () {
        throw new ApiError('No se pudo conectar con el servidor. Revisa tu conexión e inténtalo de nuevo.', 0);
      })
      .then(function (resp) {
        return resp.json().catch(function () { return null; }).then(function (data) {
          if (!resp.ok) {
            var msg = (data && data.error) || 'Ocurrió un error inesperado (' + resp.status + ').';
            throw new ApiError(msg, resp.status, data && data.detalles);
          }
          return data;
        });
      });
  }

  function query(params) {
    var p = new URLSearchParams();
    Object.keys(params || {}).forEach(function (k) {
      if (params[k] !== undefined && params[k] !== null && String(params[k]).trim() !== '') {
        p.append(k, params[k]);
      }
    });
    var s = p.toString();
    return s ? '?' + s : '';
  }

  global.Api = {
    ApiError: ApiError,
    listarCursos: function (filtros) { return request('GET', '/cursos' + query(filtros)); },
    obtenerCurso: function (id) { return request('GET', '/cursos/' + encodeURIComponent(id)); },
    darDeBajaCurso: function (id) { return request('DELETE', '/cursos/' + encodeURIComponent(id)); }
  };
})(window);
