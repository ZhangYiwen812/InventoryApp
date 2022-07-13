<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifMail extends Mailable
{
    use Queueable, SerializesModels;
    //这里定义邮箱的发送源
    // public $from = [
    //     [
    //         'address' => 'email@kong-qi.com',
    //         'name' => '测试邮箱'
    //     ]
    // ];
    public $validcode;

    /**
     * Create a new message instance.
     * @return void
     */
    public function __construct($validcode)
    {
        $this->validcode = $validcode;
    }

    /**
     * Build the message.
     * @return $this
     */
    public function build()
    {
        return $this->view('verifmail')->subject('杨柳盘点系统');
    }
}
