<?php 
use PHPUnit\Framework\TestCase;
use Scaleum\Stdlib\Base\InitTrait;

class InitTraitTest extends TestCase{
    use InitTrait;
    protected $var;

    public function testInit(){
        $this->init(['var' => 'value'], $this);
        $this->assertTrue($this->var == 'value');
    }   
}