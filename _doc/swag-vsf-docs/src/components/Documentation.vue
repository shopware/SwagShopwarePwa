<template>
    <div id="swagger-doc">
        
    </div>
</template>

<script>

import SwaggerUI from 'swagger-ui'
import 'swagger-ui/dist/swagger-ui.css'

export default {
    mounted: function() {
        SwaggerUI({
            dom_id: '#swagger-doc',
            url: 'swagger.yaml'
        });
    }
};

</script>