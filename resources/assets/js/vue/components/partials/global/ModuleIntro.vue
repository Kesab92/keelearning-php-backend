<template>
  <div class="c-moduleIntro">
    <h1 class="c-moduleIntro__heading">
      <slot name="title"></slot>
    </h1>
    <div class="c-moduleIntro__description">
      <slot name="description"></slot>
    </div>
    <div class="c-moduleIntro__links">
      <slot name="links"></slot>
    </div>
  </div>
</template>
