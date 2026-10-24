// public/js/app.js
// Comportamiento de las pantallas que consumen la API propia con fetch:
//  - Home: lista de cursos con pestañas (recientes / mejor calificados / más vendidos)
//  - Buscador: filtros sin recargar la página
//  - Mis cursos (instructor): baja lógica con confirmación
document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  var MESES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
  var fmtMoneda = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });

  function formatoFecha(iso) {
    var d = new Date(iso);
    if (isNaN(d)) { return '—'; }
    return ('0' + d.getDate()).slice(-2) + ' ' + MESES[d.getMonth()] + ' ' + d.getFullYear();
  }

  function el(tag, clase, texto) {
    var e = document.createElement(tag);
    if (clase) { e.className = clase; }
    if (texto !== undefined) { e.textContent = texto; }
    return e;
  }

  function mostrarMensaje(contenedor, texto, tipo) {
    contenedor.innerHTML = '';
    if (!texto) { return; }
    var div = el('div', 'alert alert-' + (tipo || 'error'), texto);
    div.setAttribute('role', 'alert');
    contenedor.appendChild(div);
  }

  function tarjetaCurso(c) {
    var a = el('a', 'card');
    a.href = '/curso/' + c.id;
    if (c.imagen) {
      var img = el('img', 'card-img');
      img.src = c.imagen;
      img.alt = 'Portada de ' + c.titulo;
      img.loading = 'lazy';
      a.appendChild(img);
    }
    a.appendChild(el('h3', '', c.titulo));
    a.appendChild(el('p', 'card-meta', c.categoria + ' · ' + c.instructor));
    var cal = c.calificacion_promedio !== null ? '★ ' + c.calificacion_promedio.toFixed(1) : 'Sin calificaciones';
    a.appendChild(el('p', 'card-meta', cal + ' · ' + c.alumnos + ' alumnos'));
    a.appendChild(el('p', 'card-precio', c.gratis ? 'Gratis' : fmtMoneda.format(c.costo)));
    a.appendChild(el('p', 'card-meta', 'Publicado el ' + formatoFecha(c.fecha_publicacion)));
    return a;
  }

  function pintarCursos(grid, mensaje, cursos) {
    grid.innerHTML = '';
    if (!cursos.length) {
      mostrarMensaje(mensaje, 'No se encontraron cursos con esos criterios.', 'info');
      return;
    }
    mostrarMensaje(mensaje, '');
    cursos.forEach(function (c) { grid.appendChild(tarjetaCurso(c)); });
  }

  function cargar(filtros, grid, mensaje) {
    grid.setAttribute('aria-busy', 'true');
    mostrarMensaje(mensaje, 'Cargando cursos…', 'info');
    return Api.listarCursos(filtros)
      .then(function (resp) { pintarCursos(grid, mensaje, resp.data); })
      .catch(function (err) {
        grid.innerHTML = '';
        var texto = err.message;
        if (err.detalles && Object.keys(err.detalles).length) {
          texto += ' ' + Object.keys(err.detalles).map(function (k) { return err.detalles[k]; }).join(' ');
        }
        mostrarMensaje(mensaje, texto, 'error');
      })
      .then(function () { grid.removeAttribute('aria-busy'); });
  }

  // ---------- Home ----------
  var homeGrid = document.getElementById('home-cursos');
  if (homeGrid) {
    var homeMsg = document.getElementById('home-mensaje');
    var tabs = document.querySelectorAll('#home-tabs [data-orden]');
    var cargarHome = function (orden) { cargar({ orden: orden, limite: 12 }, homeGrid, homeMsg); };
    tabs.forEach(function (t) {
      t.addEventListener('click', function () {
        tabs.forEach(function (x) { x.classList.remove('active'); });
        t.classList.add('active');
        cargarHome(t.getAttribute('data-orden'));
      });
    });
    cargarHome('recientes');
  }

  // ---------- Buscador ----------
  var form = document.getElementById('form-buscador');
  if (form) {
    var grid = document.getElementById('buscador-resultados');
    var msg = document.getElementById('buscador-mensaje');
    var leerFiltros = function () {
      var fd = new FormData(form);
      return {
        q: fd.get('q'), categoria_id: fd.get('categoria_id'), instructor: fd.get('instructor'),
        desde: fd.get('desde'), hasta: fd.get('hasta'), limite: 50
      };
    };
    form.addEventListener('submit', function (e) {
      e.preventDefault(); // sin recargar la página
      var filtros = leerFiltros();
      var qs = new URLSearchParams();
      ['q', 'categoria_id', 'instructor', 'desde', 'hasta'].forEach(function (k) {
        if (filtros[k]) { qs.append(k, filtros[k]); }
      });
      history.replaceState(null, '', '/buscador' + (qs.toString() ? '?' + qs.toString() : ''));
      cargar(filtros, grid, msg);
    });
    cargar(leerFiltros(), grid, msg);
  }

  // ---------- Mis cursos: baja lógica ----------
  var tabla = document.getElementById('tabla-mis-cursos');
  if (tabla) {
    var msgCursos = document.getElementById('cursos-mensaje');
    tabla.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-baja-curso]');
      if (!btn) { return; }
      if (!confirm('¿Seguro que deseas dar de baja este curso? Dejará de mostrarse en el portal.')) { return; }
      var id = btn.getAttribute('data-baja-curso');
      btn.disabled = true;
      Api.darDeBajaCurso(id)
        .then(function () {
          var fila = tabla.querySelector('tr[data-curso-id="' + id + '"]');
          if (fila) {
            fila.querySelector('.celda-estado').textContent = 'Dado de baja';
            fila.querySelector('.celda-acciones').textContent = '—';
          }
          mostrarMensaje(msgCursos, 'El curso fue dado de baja correctamente.', 'success');
        })
        .catch(function (err) {
          btn.disabled = false;
          mostrarMensaje(msgCursos, err.message, 'error');
        });
    });
  }
});
