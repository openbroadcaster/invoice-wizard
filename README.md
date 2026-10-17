# OBInvoiceModule

## Upgrading an older install

Older installs registered the Ad/PSA playlist slots with the model name `AdsModel`, which core can't load, so the slots never added anything to a playlist. After updating the code, fix the two rows once:

```sql
UPDATE playlists_items_types SET callback_model = 'Ads' WHERE name IN ('ad_slot_30', 'psa_slot_30');
```

New installs register the right name. Reinstalling would also fix it, but it recreates the module's permissions, so groups would lose the access they were given.
