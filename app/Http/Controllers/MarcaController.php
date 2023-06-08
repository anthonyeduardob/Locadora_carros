<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MarcaController extends Controller
{
    public function __construct(Marca $marca){
        $this->marca = $marca;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $marcas = $this->marca->all();
        return response()->json($marcas, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate($this->marca->rules(), $this->marca->feedback());
        /*     
            STATELESS -> quando o formulário não passa pela validação, o 
            usuário é empurrado de volta para a view do formulário para 
            que possa corrigir o erro cometido, mas em um API não existe 
            uma tela previamente usada para o formulário.

            Para corrigir esse problema é necessário que o cliente passe
            no cabeçalho da requisição " Accept - Application/json
        */
        $image = $request->file('imagem');
        $image_urn = $image->store('imagens', 'public');
        
        $marca = $this->marca->create([
            'nome' => $request->nome,
            'imagem' => $image_urn
        ]);
        
        return response()->json($marca, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $marca = $this->marca->find($id);
        if($marca === null) {
            return response()->json(['erro' => 'Recurso pesquisado não existe!'], 404); //json
        }
        return response()->json($marca, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Marca  $marca
     * @return \Illuminate\Http\Response
     */
    public function edit(Marca $marca)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        /* 
        print_r($request->all());//os dados atualizados
        echo '<hr>';
        print_r($marca->getAttributes());//os dados antigos
        */
        $marca = $this->marca->find($id);
        
        if($marca === null) {
            return response()->json(['erro' => 'Impossivel ralizar a atualização. O recurso solicitado não existe!'], 404);
        }

        if($request->method() === 'PATCH') {
            $regrasDinamicas = array();
            //percorrendo todas as regras definidas no Model
            foreach($marca->rules() as $input => $regra){

                //coletar apenas as regras aplicáveis aos parâmetros parciais da requisição PATCH
                if(array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regra;
                }
            }

            $request->validate($regrasDinamicas, $marca->feedback());

        } else {
            $request->validate($marca->rules(), $marca->feedback());
        }
        
        //remove o arquivo antigo caso um novo arquivo tenha sido enviado no request
        if($request->file('imagem')) {
            Storage::disk('public')->delete($marca->imagem);    
        }

        $image = $request->file('imagem');
        $image_urn = $image->store('imagens', 'public');
                
        $marca->update([
            'nome' => $request->nome,
            'imagem' => $image_urn
        ]);

        return response()->json($marca, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $marca = $this->marca->find($id);

        if($marca === null) {
            return response()->json(['erro' => 'Impossivel ralizar a exclusão. O recurso solicitado não existe!'], 404);
        }

        //remove o arquivo antigo
        Storage::disk('public')->delete($marca->imagem); 

        $marca->delete();
        return response()->json(['msg' => 'A marca foi removida com sucesso!'], 200);
    }
}
