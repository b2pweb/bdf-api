<?php

namespace Bdf\Soap\Server;

use Bdf\Api\Server\Request\AbstractInputRequest;
use SoapFault;
use DOMDocument;

/**
 * Soap Request
 */
class Request extends AbstractInputRequest
{
    /**
     * {@inheritdoc}
     */
    public function validate(): void
    {
        if (strlen($this->rawBody) == 0) {
            throw new SoapFault('Sender', 'Invalid XML');
        }

        $internalErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $dom = new DOMDocument();
//        $dom->validateOnParse = true;

        if (!$dom->loadXML($this->rawBody, LIBXML_NONET | (defined('LIBXML_COMPACT') ? LIBXML_COMPACT : 0))) {
            throw new SoapFault('Sender', 'Invalid XML');
        }

//        $dom->normalizeDocument();

        libxml_use_internal_errors($internalErrors);

        foreach ($dom->childNodes as $child) {
            if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                throw new SoapFault('Sender', 'Invalid XML: Detected use of illegal DOCTYPE');
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function parseRawBody(): void
    {
        
    }
}
