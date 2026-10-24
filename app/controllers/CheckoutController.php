<?php
class CheckoutController extends Controller
{
    private CursoModel $cursos;
    private InscripcionModel $inscripciones;

    public function __construct()
    {
        $this->cursos = new CursoModel();
        $this->inscripciones = new InscripcionModel();
    }

    // Muestra la pantalla de pago
    public function mostrarCheckout(array $params): void
    {
        $cursoId = (int) $params['id'];
        $curso = $this->cursos->buscarPorId($cursoId);
        
        if (!$curso) { $this->notFound(); }

        // Verificamos que no lo haya comprado ya
        if ($this->inscripciones->yaInscrito($cursoId, $_SESSION['user_id'])) {
            $this->redirect('estudiante/dashboard?msg=ya_inscrito');
        }

        // Si viene un error de PayPal, lo mostramos en la vista (opcional)
        $error = $_GET['error'] ?? null;

        $this->view('student/checkout', compact('curso', 'error'));
    }

    // --------------------------------------------------------
    // 1. LÓGICA DE PAYPAL: Crear la orden y redirigir
    // --------------------------------------------------------
    public function pagarConPayPal(array $params): void
    {
        $cursoId = (int) $params['id'];
        $curso = $this->cursos->buscarPorId($cursoId);

        if (!$curso) { $this->notFound(); }

        $paypal = new PayPalService();
        $orden = $paypal->crearOrden((float)$curso['costo'], "Curso: " . $curso['titulo'], $cursoId);

        if (isset($orden['links'])) {
            // Buscamos el enlace de aprobación de PayPal y redirigimos
            foreach ($orden['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    header('Location: ' . $link['href']);
                    exit;
                }
            }
        }
        
        // Si falló la creación de la orden, regresamos con error
        $this->redirect('checkout/'.$cursoId.'?error=paypal_fallo');
    }

    // --------------------------------------------------------
    // 2. LÓGICA DE PAYPAL: Capturar el pago al regresar
    // --------------------------------------------------------
        public function capturarPago(array $params): void
    {
        $cursoId = (int) $params['id'];
        $token = $_GET['token'] ?? null;

        if (!$token) {
            $this->redirect('checkout/'.$cursoId.'?error=no_token');
        }

        $paypal = new PayPalService();
        $resultado = $paypal->capturarOrden($token);

        if (isset($resultado['status']) && $resultado['status'] === 'COMPLETED') {
            $curso = $this->cursos->buscarPorId($cursoId);
            
            $this->inscripciones->crear([
                'curso_id'      => $cursoId,
                'estudiante_id' => $_SESSION['user_id'],
                'precio'        => $curso['costo'],
                'forma_pago'    => 'PayPal'
            ]);
            
            // CORRECCIÓN: Quitar la diagonal inicial si la tiene
            $this->redirect('estudiante/dashboard?msg=compra_exitosa');
            
        } else {
            $this->redirect('checkout/'.$cursoId.'?error=pago_fallido');
        }
    }
    // --------------------------------------------------------
    // 3. MÉTODO DE RESPALDO (Simulación de pago con Tarjeta)
    // --------------------------------------------------------
    public function procesarPago(array $params): void
    {
        $cursoId = (int) $params['id'];
        $curso = $this->cursos->buscarPorId($cursoId);
        $metodoPago = $this->input('metodo_pago');

        if (!$curso) { $this->notFound(); }

        $this->inscripciones->crear([
            'curso_id'      => $cursoId,
            'estudiante_id' => $_SESSION['user_id'],
            'precio'        => $curso['costo'],
            'forma_pago'    => $metodoPago
        ]);

        $this->redirect('estudiante/dashboard?msg=compra_exitosa');
    }
}