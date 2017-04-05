<?php

namespace awheel\Http;

use ArrayAccess;
use JsonSerializable;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * http 响应
 *
 * @package awheel
 */
class Response extends SymfonyResponse
{
    /**
     * 设置响应类型
     *
     * @param $contentType
     *
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->headers->set('Content-Type', $contentType);

        return $this;
    }

    /**
     * 设置 body
     *
     * @param $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        if (is_array($content) || $content instanceof ArrayAccess || $content instanceof JsonSerializable) {
            $this->headers->add(['Content-Type' => 'application/json']);
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        }

        parent::setContent($content);

        return $this;
    }
}
