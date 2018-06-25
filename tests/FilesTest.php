<?php
/*
 * This software is licensed under the MIT License. Please see LICENSE for more details.
 */

namespace Aesonus\Tests;
use org\bovigo\vfs\vfsStream;

/**
 * Description of FilesTest
 *
 * @author Aesonus <corylcomposinger at gmail.com>
 */
class FilesTest extends \Aesonus\TestLib\BaseTestCase
{

    protected $testObj;
    protected $vfsStream;
    protected $root = 'vfs://root';

    protected function setUp()
    {
        $this->testObj = $this->getMockForTrait(\Aesonus\Paladin\Paladins\Files::class);
        $this->vfsStream = vfsStream::setup();
        vfsStream::create([
            'dir' => [],
            'file' => "I'm a file!"
        ]);
        parent::setUp();
    }

    /**
     * @test
     * @dataProvider validateFileReturnsTrueIfFileIsValidDataProvider
     */
    public function validateFileReturnsTrueIfFileIsValid($filename)
    {
        $this->assertTrue($this->invokeMethod($this->testObj, 'validateFile', [$filename]));
    }

    public function validateFileReturnsTrueIfFileIsValidDataProvider()
    {
        return [
            [$this->root . '/file']
        ];
    }

    /**
     * @test
     * @dataProvider validateFileReturnsFalseIfFileIsInvalidDataProvider
     */
    public function validateFileReturnsFalseIfFileIsInvalid($filename)
    {
        $this->assertFalse($this->invokeMethod($this->testObj, 'validateFile', [$filename]));
    }

    public function validateFileReturnsFalseIfFileIsInvalidDataProvider()
    {
        return [
            [$this->root . '/dir'],
            [23],
            ['fndsafkjh']
        ];
    }
    
    /**
     * @test
     * @dataProvider validateDirReturnsTrueOnValidDirectoryDataProvider
     */
    public function validateDirReturnsTrueOnValidDirectory($filename)
    {
        $this->assertTrue($this->invokeMethod($this->testObj, 'validateDir', [$filename]));
    }

    public function validateDirReturnsTrueOnValidDirectoryDataProvider()
    {
        return [
            [$this->root . '/dir'],
        ];
    }
    
    /**
     * @test
     * @dataProvider validateDirReturnsFalseOnInvalidDirectoryDataProvider
     */
    public function validateDirReturnsFalseOnInvalidDirectory($filename)
    {
        $this->assertFalse($this->invokeMethod($this->testObj, 'validateDir', [$filename]));
    }

    public function validateDirReturnsFalseOnInvalidDirectoryDataProvider()
    {
        return [
            [$this->root . '/file'],
            ['poopystring'],
            [new \stdClass()]
        ];
    }
}
