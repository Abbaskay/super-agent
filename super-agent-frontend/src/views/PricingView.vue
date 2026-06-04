<template>
  <div class="pricing-page">
    <header class="pricing-header">
      <router-link :to="{ name: 'Welcome' }" class="pricing-back">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Back
      </router-link>
      <div class="pricing-header-content">
        <h1 class="pricing-title">Simple, transparent pricing</h1>
        <p class="pricing-subtitle">Choose the plan that fits your needs</p>
      </div>
    </header>

    <main class="pricing-main">
      <div class="plans">
        <div v-for="plan in plans" :key="plan.name" :class="['plan-card', { popular: plan.popular }]">
          <div v-if="plan.popular" class="plan-badge">Most Popular</div>
          <div class="plan-name">{{ plan.name }}</div>
          <div class="plan-price">
            <span class="plan-amount">${{ plan.price }}</span>
            <span class="plan-period">/month</span>
          </div>
          <p class="plan-desc">{{ plan.desc }}</p>
          <ul class="plan-features">
            <li v-for="f in plan.features" :key="f" class="plan-feature">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--accent-emerald)" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
              {{ f }}
            </li>
          </ul>
          <button :class="['plan-btn', { primary: plan.popular }]" :disabled="plan.disabled">
            {{ plan.cta }}
          </button>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
export default {
  name: 'PricingView',
  mounted() {
    console.log('[PricingView] mounted')
  },
  data() {
    return {
      plans: [
        {
          name: 'Free',
          price: 0,
          desc: 'For getting started with basic AI assistance',
          features: ['5 conversations per day', 'General Q&A', 'Basic agent routing', 'Markdown responses'],
          cta: 'Get Started',
          popular: false,
          disabled: false,
        },
        {
          name: 'Pro',
          price: 19,
          desc: 'For professionals who need advanced AI capabilities',
          features: ['Unlimited conversations', 'Priority AI routing', 'All 3 specialized agents', 'Document & slide generation', 'Fact-checking with sources', 'Priority support'],
          cta: 'Coming Soon',
          popular: true,
          disabled: true,
        },
        {
          name: 'Business',
          price: 49,
          desc: 'For teams requiring full AI workspace capabilities',
          features: ['Everything in Pro', 'Team collaboration', 'Custom agent configurations', 'API access', 'Dedicated support', 'Custom integrations'],
          cta: 'Coming Soon',
          popular: false,
          disabled: true,
        },
      ],
    }
  },
}
</script>

<style scoped>
.pricing-page { height: 100dvh; overflow-y: auto; background: var(--bg-primary); }
.pricing-header {
  padding: 24px 24px 0; max-width: 800px; margin: 0 auto;
}
.pricing-back {
  display: inline-flex; align-items: center; gap: 5px; font-size: 13px;
  color: var(--text-tertiary); text-decoration: none; margin-bottom: 20px;
  transition: color 0.15s;
}
.pricing-back:hover { color: var(--text-secondary); }
.pricing-header-content { text-align: center; margin-bottom: 40px; }
.pricing-title { font-size: 36px; font-weight: 700; letter-spacing: -0.03em; color: var(--text-primary); margin-bottom: 8px; }
.pricing-subtitle { font-size: 16px; color: var(--text-tertiary); }
.pricing-main { padding: 0 24px 40px; }
.plans { display: flex; gap: 16px; max-width: 800px; margin: 0 auto; align-items: stretch; }
.plan-card {
  flex: 1; padding: 28px 24px; border-radius: var(--radius-lg);
  background: var(--bg-secondary); border: 1px solid var(--border-subtle);
  display: flex; flex-direction: column; position: relative; transition: all 0.2s;
}
.plan-card.popular {
  background: var(--bg-elevated); border-color: rgba(94,158,255,0.15);
  box-shadow: 0 0 0 1px rgba(94,158,255,0.06), 0 8px 32px rgba(0,0,0,0.15);
  transform: scale(1.04);
}
.plan-badge {
  position: absolute; top: -10px; left: 50%; transform: translateX(-50%);
  padding: 4px 14px; border-radius: var(--radius-full);
  background: var(--accent-blue); color: white; font-size: 11px; font-weight: 600;
  letter-spacing: 0.03em; white-space: nowrap;
}
.plan-name { font-size: 16px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; }
.plan-price { margin-bottom: 12px; }
.plan-amount { font-size: 36px; font-weight: 700; color: var(--text-primary); letter-spacing: -0.03em; }
.plan-period { font-size: 14px; color: var(--text-tertiary); }
.plan-desc { font-size: 13px; color: var(--text-tertiary); line-height: 1.5; margin-bottom: 20px; }
.plan-features { list-style: none; padding: 0; margin: 0 0 24px; flex: 1; }
.plan-feature {
  display: flex; align-items: center; gap: 8px; font-size: 13px;
  color: var(--text-secondary); padding: 5px 0;
}
.plan-btn {
  padding: 10px; border-radius: var(--radius-md); border: 1px solid var(--border-default);
  background: transparent; color: var(--text-secondary); font-size: 14px; font-weight: 600;
  cursor: pointer; transition: all 0.15s; font-family: inherit; text-align: center;
}
.plan-btn:hover { background: var(--bg-glass); color: var(--text-primary); }
.plan-btn.primary { background: var(--accent-blue); color: white; border-color: transparent; }
.plan-btn.primary:hover { background: #4a8be7; }
.plan-btn:disabled { opacity: 0.5; cursor: not-allowed; }

@media (max-width: 768px) {
  .plans { flex-direction: column; gap: 16px; }
  .plan-card.popular { transform: none; }
  .pricing-title { font-size: 28px; }
}
</style>
