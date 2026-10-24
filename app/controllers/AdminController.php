<?php
// app/controllers/AdminController.php
// Pantallas del rol "administrador": categorías, usuarios (bloqueo/desbloqueo),
// comentarios reportados, reporte de usuarios.

class AdminController extends Controller
{
    public function dashboard(): void
    {
        // TODO (2da/3ra entrega): reemplazar por conteos reales
        $resumen = [
            'usuarios_totales'    => 215,
            'instructores'        => 18,
            'estudiantes'         => 197,
            'cursos_activos'      => 34,
            'comentarios_reportados' => 3,
        ];
        $this->view('admin/dashboard', compact('resumen'));
    }

    public function categorias(): void
    {
        $categorias = [
            ['nombre' => 'IT & Software', 'descripcion' => 'Programación, bases de datos, DevOps.', 'creado_por' => 'admin@portal.com', 'fecha_creacion' => '01 ago 2026'],
            ['nombre' => 'Marketing', 'descripcion' => 'Marketing digital, redes sociales, SEO.', 'creado_por' => 'admin@portal.com', 'fecha_creacion' => '01 ago 2026'],
            ['nombre' => 'Design', 'descripcion' => 'Diseño gráfico, UX/UI, ilustración.', 'creado_por' => 'admin@portal.com', 'fecha_creacion' => '03 ago 2026'],
            ['nombre' => 'Negocios', 'descripcion' => 'Emprendimiento, finanzas, liderazgo.', 'creado_por' => 'admin@portal.com', 'fecha_creacion' => '05 ago 2026'],
        ];
        $this->view('admin/categorias', compact('categorias'));
    }

    public function usuarios(): void
    {
        $usuarios = [
            ['email' => 'ana.torres@portal.com', 'nombre_completo' => 'Ana Torres', 'rol' => 'instructor', 'activo' => true],
            ['email' => 'carlos.ruiz@portal.com', 'nombre_completo' => 'Carlos Ruiz', 'rol' => 'instructor', 'activo' => true],
            ['email' => 'maria.lopez@portal.com', 'nombre_completo' => 'María López', 'rol' => 'estudiante', 'activo' => true],
            ['email' => 'jorge.pena@portal.com', 'nombre_completo' => 'Jorge Peña', 'rol' => 'estudiante', 'activo' => false],
        ];
        $this->view('admin/usuarios', compact('usuarios'));
    }

    public function reporteUsuarios(): void
    {
        $tipo = $this->input('tipo', 'instructor'); // instructor | estudiante
        $reporte = $tipo === 'instructor' ? [
            ['usuario' => 'ana.torres@portal.com', 'nombre' => 'Ana Torres', 'fecha_ingreso' => '15 mar 2026', 'cursos_ofrecidos' => 3, 'ganancias' => '$18,450.00'],
            ['usuario' => 'carlos.ruiz@portal.com', 'nombre' => 'Carlos Ruiz', 'fecha_ingreso' => '02 abr 2026', 'cursos_ofrecidos' => 2, 'ganancias' => '$9,120.00'],
        ] : [
            ['usuario' => 'maria.lopez@portal.com', 'nombre' => 'María López', 'fecha_ingreso' => '12 ago 2026', 'cursos_inscritos' => 4, 'porcentaje_terminados' => '25%'],
            ['usuario' => 'jorge.pena@portal.com', 'nombre' => 'Jorge Peña', 'fecha_ingreso' => '20 ago 2026', 'cursos_inscritos' => 2, 'porcentaje_terminados' => '50%'],
        ];
        $this->view('admin/reporte_usuarios', compact('tipo', 'reporte'));
    }
}
