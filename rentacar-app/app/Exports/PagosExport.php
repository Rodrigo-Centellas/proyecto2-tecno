<?php
namespace App\Exports;

use App\Models\Pago;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromView;

class PagosExport implements FromView
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $pagos = Pago::whereBetween('fecha', [$this->request->desde, $this->request->hasta])            
            ->get();

        return view('reportes.pagos_excel', compact('pagos'));
    }
}
