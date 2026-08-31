<template>
  <div class="form-wrapper" :class="{ 'form-centered': centered }">
    <div class="form-sidebar">
      <div class="form-icon">💬</div>
      <div class="form-icon-title">Консультация</div>
      <div class="form-icon-sub">специалиста</div>
    </div>
    <div class="form-content">
      <FormRenderer :formId="formId" :centered="false" />
    </div>
  </div>
</template>

<script setup lang="ts">
import FormRenderer from './FormRenderer.vue';
defineProps<{ formId: number; centered?: boolean; }>();
</script>