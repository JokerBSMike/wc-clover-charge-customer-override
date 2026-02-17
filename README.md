# Clover for WooCommerce Customer Override

This MU-plugin injects WooCommerce billing customer data into Clover `/v1/charges` API requests when using the **Clover for WooCommerce** plugin from Kestrel.

By default, the Clover for WooCommerce plugin does not send the `customer` object when creating a charge. As a result, customer names do not appear in Clover’s transaction list.

This plugin corrects that behavior without modifying core plugin files.

---

## Problem

When using:

Clover for WooCommerce  

Transactions appear in Clover without customer names.

The official Clover Payments plugin includes:

```json
"customer": {
  "first_name": "...",
  "last_name": "..."
}
```

The Clover for WooCommerce plugin does not.

---

## Solution

This plugin hooks into the SkyVerge API request filter:

```
wc_first_data_clover_credit_card_http_request_args
```

It detects calls to:

```
v1/charges
```

And injects:

```json
"customer": {
  "first_name": "...",
  "last_name": "...",
  "email": "...",
  "phone": "..."
}
```

using WooCommerce billing data.

---

## Installation

### Recommended (MU-plugin)

1. Create directory if it does not exist:

```
/wp-content/mu-plugins/
```

2. Place file:

```
clover-charge-customer-info.php
```

3. No activation required.

MU-plugins cannot be accidentally disabled and survive theme changes.

---

## Compatibility

Tested with:

- WordPress 6.9
- WooCommerce 10.4.3
- Clover for WooCommerce 5.3.2

Works with:

```
section=first_data_clover_credit_card
```

---

## Why This Approach

- No core plugin edits
- Survives updates
- Targets only `v1/charges`
- Does not interfere with tokenization or saved cards
- Leaves existing customer payload untouched if already present

---

## Verification

1. Place a test order.
2. Open Clover Dashboard → Transactions.
3. Confirm customer name appears.

Only new transactions will reflect the change.

---

## Author

Joker Business Solutions

---

## License

MIT
