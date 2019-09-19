
<!-- Start Gmail Promo Tab annotations code -->
<div itemscope itemtype="http://schema.org/Organization">
    <meta itemprop="name" content="Millesima" />
    <meta itemprop="logo" content="https://static.millesima.com/s3/contrib/common/promotion-card/logo.png" />
</div>
<div itemscope itemtype="http://schema.org/EmailMessage">
    <meta itemprop="subjectLine" content="{$desc.titre}" />
</div>
<div itemscope itemtype="http://schema.org/DiscountOffer">
    <meta itemprop="description" content="{$promotionCardDescription}" />
    <meta itemprop="discountCode" content="{$promotionCardDiscountCode}" />
    <meta itemprop="availabilityStarts" content="{$promotionCardDateStart}" />
    <meta itemprop="availabilityEnds" content="{$promotionCardDateEnd}" />
</div>
{if $promotionCardImageLink}
<div itemscope itemtype="http://schema.org/PromotionCard">
    <meta itemprop="image" content="{$promotionCardImageLink}" />
</div>
{/if}
<!-- End Gmail Promo Tab annotations code -->
