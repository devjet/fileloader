<?php

namespace devjet\fileloader;

use PHPUnit\Framework\TestCase;



class LoaderTest extends TestCase
{

    /**
     * @expectedException Exception
     */
    public function testURLException()
    {
        FileLoader::getLoader()
            ->setLoadURL('http://images.all-free-download.comimages/graphiclarge/cat_having_a_stretch_205116.jpg')
            ->addExtension('tiff', 'zip')
            ->load();

    }

    /**
     * @expectedException Exception
     */
    public function testSavePathCreateException()
    {
        FileLoader::getLoader()
            ->setLoadURL('http://images.all-free-download.com/images/graphiclarge/cat_having_a_stretch_205116.jpg')
            ->setSavePath('folder')
            ->load();

    }


    /**
     * @expectedException Exception
     */
    public function testExtensionException()
    {
        FileLoader::getLoader()
            ->setLoadURL('http://php.net/manual/ru/function.file.php')
            ->load();

    }



}