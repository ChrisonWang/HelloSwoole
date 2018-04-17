<?php
class Tick
{
    private $str = "Say Hello";
    public function onAfter( $word=false )
    {
        if($word){
            echo $word."\n";
        }
        else {
            echo $this->str."\n";
        }
    }
}

$tick = new Tick();
$word = "Bye Bye!";
swoole_timer_after(1000, array($tick, "onAfter"));

swoole_timer_after(3000, function() use ($tick, $word){
    $tick->onAfter($word);
});
