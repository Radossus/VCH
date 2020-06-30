<?php


namespace App\Presenters;


class HomePageNew
{
    private $datum;
    private $title;
    private $content;
    private $url;

    public function __construct($datum,$title,$content,$url)
    {
        $this->datum = $datum;
        $this->title = $title;
        $this->content = $content;
        $this->url = $url;
    }

    public function setHomePageNew():void {

    }

    public function Datum(){
        return $this->datum;
    }

}