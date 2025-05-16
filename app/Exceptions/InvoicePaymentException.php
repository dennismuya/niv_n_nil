<?php

namespace App\Exceptions;

use Exception;
use http\Env\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class InvoicePaymentException extends Exception
{

    /**
     * Report the exception
     *
     * @param Request $request
     * @return void
     */

    public function report(){

        Log::channel('slack')->critical('Invoice Payment Error',[
            'error'=> $this->getMessage(),
            'line'=> $this->getLine(),
            'code'=>$this->getCode(),
            'trace'=>$this->getTrace(),
            'user'=>auth()->user()

        ]);
    }

    /**
     * Render the exception as an http responce
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function render(){

        return new JsonResponse([

            'message'=>'invoice payment failed'
        ],$this->getCode());


    }






}
