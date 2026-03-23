<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

enum ErrorType {
    'bad_request',
    'unauthorized',
    'access_denied',
    'no_such_record',
    'record_exists',
};

defineProps<{
    error: ErrorType,
    message: string,
}>();
</script>

<template>
    <article>
        <p>{{ message }}</p>
        <p><Link href="/">Back to home.</Link></p>
    </article>
</template>
