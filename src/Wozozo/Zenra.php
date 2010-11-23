<?php
/**
 * Wozozo::Zenra - zenrize Japanese text with Yahoo API.
 *
 * PHP version 5.3
 *
 * Copyright (c) 2010 Shinya Ohyanagi, All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Shinya Ohyanagi nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @use       Wozozo\Zenra\Exception
 * @category  Wozozo
 * @package   Wozozo\Zenra
 * @version   $id$
 * @copyright (c) 2010 Shinya Ohyanagi
 * @author    Shinya Ohyanagi <sohyanagi@gmail.com>
 * @license   New BSD License
 */
namespace Wozozo;
use Wozozo\Zenra\Exception;

/**
 * Wozozo::Zenra - zenrize Japanese text with Yahoo API.
 *
 * @use       Wozozo\Zenra\Exception
 * @category  Wozozo
 * @package   Wozozo\Zenra
 * @version   $id$
 * @copyright (c) 2010 Shinya Ohyanagi
 * @author    Shinya Ohyanagi <sohyanagi@gmail.com>
 * @license   New BSD License

 */
class Zenra
{
    /**
     * Yahoo! Japan Developer id.
     *
     * @var mixed
     * @access private
     */
    private $_appId = null;

    /**
     * Url of Yahoo! Developer API.
     *
     * @var    mixed
     * @see    http://developer.yahoo.co.jp/webapi/jlp/ma/v1/parse.html
     * @access private
     */
    private $_baseUrl = null;

    /**
     * Position.
     *
     * @var    mixed
     * @access private
     */
    private $_position = null;

    /**
     * Text.
     *
     * @var    mixed
     * @access private
     */
    private $_text = null;

    /**
     * Client.
     *
     * @var    mixed
     * @access private
     */
    private $_client = null;

    /**
     * Constructor.
     *
     * @param  array $args
     * @access public
     * @return void
     */
    public function __construct(array $args)
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
        if (!isset($args['appid'])) {
            throw new Exception('Yahoo! Developer id not found.');
        }

        $this->_baseUrl = isset($args['base_url'])
                        ? $args['base_url']
                        : 'http://jlp.yahooapis.jp/MAService/V1/parse';
        $this->_appId    = $args['appid'];
        $this->_position = isset($args['position'])
                         ? $args['position']
                         : '動詞';
        $this->_text   = isset($args['text']) ? $args['text'] : '全裸で';
        $this->_client = New \HTTP_Request2();
    }

    /**
     * Zenrize.
     *
     * @param  mixed $sentence Text to zenrize
     * @access public
     * @return string Zenrized text
     */
    public function zenrize($sentence)
    {
        $query = array(
            'appid'    => $this->_appId,
            'sentence' => urlencode($sentence)
        );
        $tpl = '%s=%s&';
        $uri = '';
        foreach ($query as $k => $v) {
            $uri .= sprintf($tpl, $k, $v);
        }
        $uri      = $this->_baseUrl . '?' . rtrim($uri, '&');
        $client   = $this->_client;
        $response = $client->setUrl($uri)
                           ->setMethod(\HTTP_Request2::METHOD_GET)
                           ->send();

        $content  = simplexml_load_string($response->getBody());
        if (!isset($content->ma_result->word_list->word)) {
            return $sentence;
        }
        $words = $content->ma_result->word_list->word;

        $result = '';
        foreach ($words as $k => $v) {
            if ($v->pos->__toString() === $this->_position) {
                $result .= $this->_text;
            }
            $result .= $v->surface;
        }

        return $result;
    }

    /**
     * Autoload class.
     *
     * @param  mixed $class
     * @access public
     * @return void
     */
    public static function autoload($className)
    {
        // Autoload class.
        // http://groups.google.com/group/php-standards/web/psr-0-final-proposal
        if (!class_exists($className, false)) {
            $className = ltrim($className, '\\');
            $fileName  = '';
            $namespace = '';
            if ($lastNsPos = strripos($className, '\\')) {
                $namespace = substr($className, 0, $lastNsPos);
                $className = substr($className, $lastNsPos + 1);
                $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
            }
            $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

            require_once $fileName;
        }
    }
}
