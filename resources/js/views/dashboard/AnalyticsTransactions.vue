<script setup>
import chartInfo from '@images/cards/chart-info.png'
import creditCardSuccess from '@images/cards/credit-card-success.png'
import creditCardWarning from '@images/cards/credit-card-warning.png'
import paypalError from '@images/cards/paypal-error.png'
import walletPrimary from '@images/cards/wallet-primary.png'

const transactions = [
  {
    amount: '1,00,000',
    paymentMethod: 'NEFT',
    description: 'Quocent',
    icon: walletPrimary,
    color: 'success',
  },
  {
    amount: '50,000',
    paymentMethod: 'Cheque',
    description: 'Company ABC',
    icon: walletPrimary,
    color: 'success',
  },
  {
    amount: '1,50,000',
    paymentMethod: 'Online',
    description: 'Company ABC',
    icon: walletPrimary,
    color: 'success',
  },
  {
    amount: '1,20,500',
    paymentMethod: 'NEFT',
    description: 'Company ABC',
    icon: walletPrimary,
    color: 'success',
  },
  {
    amount: '2,50,000',
    paymentMethod: 'Cheque',
    description: 'Company ABC',
    icon: walletPrimary,
    color: 'success',
  },



]

</script>

<template>
  <VCard title="Transactions">


    <VCardText>
      <VList class="card-list">
        <VListItem v-for="item in transactions" :key="item.paymentMethod">
          <template #prepend>
            <VAvatar rounded variant="tonal" :color="item.color" :image="item.icon" class="me-3" />
          </template>

          <VListItemSubtitle class="text-disabled mb-1">
            {{ item.paymentMethod }}
          </VListItemSubtitle>
          <VListItemTitle>
            {{ item.description }}
          </VListItemTitle>

          <template #append>
            <VListItemAction>
              <span class="me-1">{{ item.amount }}</span>
              <span class="text-disabled">INR</span>
            </VListItemAction>
          </template>
        </VListItem>
      </VList>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 1.6rem;
}
</style>
