<?php

namespace Magesales\AddressValidation\Block\Adminhtml\Plugin;

class Reinit
{
    public function afterGetButtonHtml($subject, $proceed)
    {
        return $proceed . "<script>
        var event = new Event('totalsBlockReInit');
        document.dispatchEvent(event);
        </script>";
    }
}
