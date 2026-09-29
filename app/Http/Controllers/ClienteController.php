<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Exibe a lista de clientes.
     */
    public function index()
    {
        $clientes = Cliente::latest()->paginate(10);
        return view('clientes.index', compact('clientes'));
    }

    /**
     * Mostra o formulário de criação.
     */
    public function create()
    {
        return view('clientes.create');
    }

    /**
     * Salva o novo cliente no banco de dados com validação.
     */
    public function store(Request $request)
    {
        // Validação dos dados digitados pelo admin
        $dadosValidados = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:clientes,email',
            'telefone' => 'nullable|string|max:20',
            'cpf_cnpj' => 'nullable|string|max:20|unique:clientes,cpf_cnpj',
            'endereco' => 'nullable|string|max:255',
        ]);

        // Cria o cliente no banco usando o $fillable do Model
        Cliente::create($dadosValidados);

        // Redireciona de volta para a lista com uma mensagem de sucesso
        return redirect()->route('clientes.index')
                         ->with('status', 'Cliente cadastrado com sucesso!');
    }
}