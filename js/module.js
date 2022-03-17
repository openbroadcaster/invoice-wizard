/*
    Copyright 2020 OpenBroadcaster, Inc.

    This file is part of OpenBroadcaster Server.

    OpenBroadcaster Server is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    OpenBroadcaster Server is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with OpenBroadcaster Server.  If not, see <http://www.gnu.org/licenses/>.
*/

OBModules.OBAdPSASystemModule = new function()
{

   this.init = function()
   {
     this.fields = [];
     this.bill_to_fields = [];
     this.broadcast_data = [];
     this.media = {};
     this.disable_checks = false;
     OB.Callbacks.add('ready',0,OBModules.OBAdPSASystemModule.initMenu);
     OB.API.post('obadpsadsystemmodule', 'start_payment', {
       'currency': 'usd', 'amount': 500
     }, function(res) {
       console.log(res.data);
     });
   }

 	this.initMenu = function()
 	{
    OB.API.post('obadpsadsystemmodule', 'permissons_check', {'name': 'ad_psa_system_change_system_settings'}, function(res) {
      console.log(res);
      if (res.status) {
        OB.UI.addSubMenuItem('admin', 'AD/PSA system settings', 'ad_psa_system_settings', OBModules.OBAdPSASystemModule.view_settings, 1);
      }
    });
     OB.UI.addMenuItem(['ad system','ad system'],'ad_system',100);
     OB.API.post('obadpsadsystemmodule', 'permissons_check', {'name': 'ad_psa_system_view_invoices'}, function(res) {
       console.log(res);
       if (res.status) {
         OB.UI.addSubMenuItem('ad_system', 'invoices', 'view_invoices', OBModules.OBAdPSASystemModule.view_invoices, 2);
       }
     });
     OB.API.post('obadpsadsystemmodule', 'permissons_check', {'name': 'ad_psa_system_change_add_edit_remove_buyers'}, function(res) {
       console.log(res);
       if (res.status) {
         OB.UI.addSubMenuItem('ad_system', 'buyers', 'ad_psa_system_view_buyer', OBModules.OBAdPSASystemModule.view_buyers, 4);
       }
     });
     OB.API.post('obadpsadsystemmodule', 'permissons_check', {'name': 'ad_psa_system_change_add_edit_remove_company'}, function(res) {
       console.log(res);
       if (res.status) {
         OB.UI.addSubMenuItem('ad_system', 'companies', 'ad_psa_system_view_companies', OBModules.OBAdPSASystemModule.view_companies, 6);
       }
     });

     OB.API.post('obadpsadsystemmodule', 'permissons_check', {'name': 'ad_psa_system_view_edit_remove_campaigns'}, function(res) {
       console.log(res);
       if (res.status) {
         OB.UI.addSubMenuItem('ad_system', 'campaigns', 'ad_psa_system_view_edit_remove_campaigns', OBModules.OBAdPSASystemModule.view_campaigns, 7);
       }
     });
     OB.API.post('obadpsadsystemmodule', 'permissons_check', {'name': 'ad_psa_system_create_tts_messages'}, function(res) {
       console.log(res);
       if (res.status) {
         OB.UI.addSubMenuItem('media', 'Polly', 'ad_psa_system_create_tts_messages', OBModules.OBAdPSASystemModule.view_polly_tts_area, 8);
       }
     });
     OB.UI.addSubMenuItem('ad_system', 'advertisers', 'ad_psa_system_view_advertisers', OBModules.OBAdPSASystemModule.view_advertisers, 1);
     OB.UI.addSubMenuItem('ad_system', 'logs', 'ad_psa_system_view_logs', OBModules.OBAdPSASystemModule.view_logs, 1);
  }

  // Disables all buttons in the wizard (Doesn't include modals).
  this.disable_all_btns = function() {
    let btns = document.querySelectorAll('#layout_main_container button');
    for (let i = 0; i < btns.length; i++) {
      btns[i].disabled = true;
    }
  }

  this.disable_modal_btns = function(exclude=null) {
    let btns = document.querySelectorAll('#layout_modal_window button');
    for (let i = 0; i < btns.length; i++) {
      btns[i].disabled = true;
    }

    if (exclude !== null) {
      document.querySelector(`#layout_modal_window ${exclude}`).disabled = false;
    }

  }

  // Opens the logs view.

  this.view_logs = function()
  {
    OB.UI.replaceMain('modules/obadpsasystem/view_logs.html');
    OBModules.OBAdPSASystemModule.load_logs();
  }

  // Loads the log data.

  this.load_logs = function(sort_col='name')
  {
    let table = document.getElementById('logs_table_body');
    let val = localStorage.getItem('logs_table_sortby');
    if (val != null) {
      sort_col = val;
    } else {
        sort_col = 'method';
    }
    // Clear table.
    table.innerHTML = '';
    OB.API.post('obadpsadsystemmodule', 'get_logs', {
      'sort_col': sort_col,
      'sort_dir': 'asc'
    }, function(res) {
      if (res.status) {
        res.data.forEach(log_item => {
          console.log(log_item);
          OBModules.OBAdPSASystemModule.add_log_item_to_table(log_item.method, log_item.message, log_item.time);
        });
      }
    });
  }

  // Add item to the logs into the table.

  this.add_log_item_to_table = function(method, message, time)
  {
    let table = document.getElementById('logs_table_body');
    let row = table.insertRow(table.rows.length);
    let cell1 = row.insertCell(0);
    let cell2 = row.insertCell(1);
    let cell3 = row.insertCell(2);

    cell1.innerHTML = htmlspecialchars(method);
    cell2.innerHTML = htmlspecialchars(message);
    cell3.innerHTML = htmlspecialchars(time);
  }

  // Displays the current advertisers in
  // the system.

  this.view_advertisers = function()
  {
    OB.UI.replaceMain('modules/obadpsasystem/view_advertisers.html');
    OBModules.OBAdPSASystemModule.load_advertisers();
  }

  // Adds the provided advertiser info into the table.

  this.add_advertiser_to_table = function (id, name, trade_name, website, local_location,
    country, type, industry) {
    let type_text = null;
    switch (type) {
      case "1":
        type_text = 'Commercial';
        break;
      case "2":
        type_text = 'Non Profit PSA / Inkind';
        break;
      case "3":
        type_text = 'Contra / Donated';
        break;
      default:
        // This should never happen.
        type_text = 'Invalid Type';
    }
    let table = document.getElementById('advertiser_table_body');
    let row = table.insertRow(table.rows.length);
    let cell1 = row.insertCell(0);
    let cell2 = row.insertCell(1);
    let cell3 = row.insertCell(2);
    let cell4 = row.insertCell(3);
    let cell5 = row.insertCell(4);
    let cell6 = row.insertCell(5);
    let cell7 = row.insertCell(6);
    let cell8 = row.insertCell(7);
    let cell9 = row.insertCell(8);

    cell1.innerHTML = htmlspecialchars(name);
    cell2.innerHTML = htmlspecialchars(trade_name);
    cell3.innerHTML = htmlspecialchars(website);
    cell4.innerHTML = htmlspecialchars(local_location);
    cell5.innerHTML = htmlspecialchars(country);
    cell6.innerHTML = htmlspecialchars(type_text);
    cell7.innerHTML = htmlspecialchars(industry);
    cell8.innerHTML = `<button class="edit" onclick="OBModules.OBAdPSASystemModule.open_edit_advertiser_modal(${id});">Edit</button>`;
    cell9.innerHTML = `<button class="delete" onclick="OBModules.OBAdPSASystemModule.remove_advertiser(${id});">Remove</button>`;
  }

  // Loads the current advertisers into
  // the table on page load, or internal refeash.

  this.load_advertisers = function(sort_col='name')
  {
    let table = document.getElementById('advertiser_table_body');
    let val = localStorage.getItem('advertiser_table_sortby');
    if (val != null) {
      sort_col = val;
    }
    // Clear table.
    table.innerHTML = '';
    OB.API.post('obadpsadsystemmodule', 'get_advertisers', {
      'sort_col': sort_col,
      'sort_dir': 'asc'
    }, function(res) {
      if (res.status) {
        res.data.forEach(advertiser => {
          console.log(advertiser);
          OBModules.OBAdPSASystemModule.add_advertiser_to_table(advertiser.id, advertiser.name, advertiser.trade_name,
          advertiser.website, advertiser.local_location, advertiser.country, advertiser.type,
          advertiser.industry);
        });
      }
    });
  }

  // Opens a modal where the user would enter the
  // info for the new advertiser.

  this.add_new_advertiser = function()
  {
    OB.UI.openModalWindow('modules/obadpsasystem/add_advertiser.html');
  }

  // Saves the data from the modal into a db entry.

  this.save_advertiser = function()
  {
    let company_name = document.getElementById('company_name').value;
    let trade_name = document.getElementById('trade_name').value;
    let website = document.getElementById('website').value;
    let local_location = document.getElementById('local_location').value;
    let country = document.getElementById('country').value;
    let type = document.getElementById('type').value;
    let industry = document.getElementById('industry').value;

    let values = {
      'company_name': company_name,
      'trade_name': trade_name,
      'website': website,
      'local_location': local_location,
      'country': country,
      'type': type,
      'industry': industry
    };

    OB.API.post('obadpsadsystemmodule', 'save_advertiser', values, function(res) {
      console.log(res);
      if (res.status) {
        $('#ad_psa_system_status_message').obWidget('success', 'Your Advertiser has been saved!');
        OB.UI.closeModalWindow();
        OBModules.OBAdPSASystemModule.load_advertisers();
      } else {
        //$('#ad_psa_system_modal_status_message').obWidget('error', 'Your Advertiser couldn\'t be saved!');
        $('#ad_psa_system_modal_status_message').obWidget('error', res.data);
      }
    });
  }

  // Opens the edit modal for a avertiser with the passed id.

  this.open_edit_advertiser_modal = function(id)
  {
    OBModules.OBAdPSASystemModule.set_data('current_advertiser_id', id);
    OB.UI.openModalWindow('modules/obadpsasystem/edit_advertiser.html');
    setTimeout(() => {
      let company_name = document.getElementById('company_name');
      let trade_name = document.getElementById('trade_name');
      let website = document.getElementById('website');
      let local_location = document.getElementById('local_location');
      let country = document.getElementById('country');
      let type = document.getElementById('type');
      let industry = document.getElementById('industry');
      OB.API.post('obadpsadsystemmodule', 'get_advertiser', {'id': id}, function(res) {
        if (res.status) {
          let data = res.data;
              company_name.value = data.name;
              trade_name.value = data.trade_name;
              website.value = data.website;
              local_location.value = data.local_location;
              country.value = data.country;
              type.value = data.type;
              industry.value = data.industry;
        } else {
          // show error here. We shouldn't get here but just in case, it's here.
          OB.UI.closeModalWindow();
          $('#ad_psa_system_status_message').obWidget('error', 'The requested advertiser is invalid!');
        }
      });
    }, 500);
  }

  // Saves the changes from the edit modal.

  this.update_advertiser = function()
  {
    let id = OBModules.OBAdPSASystemModule.get_data('current_advertiser_id');
    let company_name = document.getElementById('company_name').value;
    let trade_name = document.getElementById('trade_name').value;
    let website = document.getElementById('website').value;
    let local_location = document.getElementById('local_location').value;
    let country = document.getElementById('country').value;
    let type = document.getElementById('type').value;
    let industry = document.getElementById('industry').value;

    let values = {'id': id, 'company_name': company_name, 'trade_name': trade_name,
                  'website': website, 'local_location': local_location, 'country': country,
                  'type': type, 'industry': industry};

    OB.API.post('obadpsadsystemmodule', 'update_advertiser', values, function(res) {
      if (res.status) {
        OB.UI.closeModalWindow();
        OBModules.OBAdPSASystemModule.load_advertisers();
        $('#ad_psa_system_status_message').obWidget('success', 'The Advertiser was updated.');
      } else {
        // Handle errors here.
        $('#ad_psa_system_modal_status_message').obWidget('error', res.msg);
      }
    });
  }

  // Remove the selected advertiser.

  this.remove_advertiser = function(id)
  {
      OB.UI.confirm('Are you sure you want to remove the selected advertiser?', () => {
        OB.API.post('obadpsadsystemmodule', 'remove_advertiser', {'id': id}, function(res) {
          if (res.status) {
            $('#ad_psa_system_status_message').obWidget('success', 'The requested Advertiser was removed.');
            OBModules.OBAdPSASystemModule.load_advertisers();
          } else {
            $('#ad_psa_system_status_message').obWidget('error', 'The requested Advertiser couldn\'t be removed.');
          }
        });
      });
  }

  // Handles resort, and reload of the advertiser table.

  this.sort_advertisers = function(sort_col)
  {
    localStorage.setItem('advertiser_table_sortby', sort_col);
    OBModules.OBAdPSASystemModule.load_advertisers(sort_col);
  }

  // Loads companies

  this.load_companies = function(sort_col)
  {
    let val = localStorage.getItem('companies_table_sortby');
    if (val != null) {
      let sort_col = val;
    } else if (sort_col == null) {
      let sort_col = 'name';
    }
    OB.API.post('obadpsadsystemmodule', 'get_companines', {
      'sort_col': sort_col,
      'sort_dir': 'asc'
    }, function(res) {
      if (res.status) {
        res.data.forEach((company) => {
          console.log(company);
          // id, name, email, street_addr, city, zip_code, phone, country
          OBModules.OBAdPSASystemModule.add_company_to_table(company.id, company.name,
                                                            company.street_addr, company.city,
                                                            company.zip_code, company.phone,
                                                            company.country);
        });
      }
    });
  }

  // Clears the table of companies.

  this.clear_companies_table_and_reload = function()
  {
    let table = document.getElementById('companies_table_body');
    table.innerHTML = '';
    setTimeout(() => {
      OBModules.OBAdPSASystemModule.load_companies();
    }, 800);
  }

  this.sort_companies = function(sort_col)
  {
    let table = document.getElementById('companies_table_body');
    table.innerHTML = '';
    localStorage.setItem('companies_table_sortby', sort_col);
    OBModules.OBAdPSASystemModule.load_companies(sort_col);
  }

  // Adds company to the table of companies.
  this.add_company_to_table = function(id, name, street_addr, city, zip_code, phone, country)
  {
    let table = document.getElementById('companies_table_body');
    let row = table.insertRow(table.rows.length);
    let cell1 = row.insertCell(0);
    let cell2 = row.insertCell(1);
    let cell3 = row.insertCell(2);
    let cell4 = row.insertCell(3);
    let cell5 = row.insertCell(4);
    let cell6 = row.insertCell(5);
    let cell7 = row.insertCell(6);
    let cell8 = row.insertCell(7);
    let cell9 = row.insertCell(8);

    cell1.innerHTML = htmlspecialchars(name);
    cell2.innerHTML = htmlspecialchars(phone);
    cell3.innerHTML = htmlspecialchars(street_addr);
    cell4.innerHTML = htmlspecialchars(city);
    cell5.innerHTML = htmlspecialchars(zip_code);
    cell6.innerHTML = htmlspecialchars(country);
    cell7.innerHTML = `<button class="edit" onclick="OBModules.OBAdPSASystemModule.open_edit_company_modal(${id});">Edit</button>`;
    cell8.innerHTML = `<button class="delete" onclick="OBModules.OBAdPSASystemModule.open_remove_company_modal(${id});">Remove</button>`;
  }

  this.open_remove_company_modal = function (id) {
    OB.UI.confirm('Are you sure you want to remove the selected item?', () => {
      OB.API.post('obadpsadsystemmodule', 'remove_company', {'id': id}, function(res) {
        $('#ad_psa_system_status_message').obWidget(res.status ? 'success' : 'error', res.msg);
        OBModules.OBAdPSASystemModule.clear_companies_table_and_reload();
      });
    }, 'Yes', 'Cancel', '', '');
  }

  this.open_edit_company_modal = function(id)
  {
    OBModules.OBAdPSASystemModule.set_data('current_company_id', id);
    OB.UI.openModalWindow('modules/obadpsasystem/edit_company.html');
    setTimeout(() => {
      /* Fix the modal msg div id later. */
      OB.API.post('obadpsadsystemmodule', 'get_all_devices', {}, function(res) {
        if (res.status) {
          let json_data = JSON.parse(res.data);
          json_data.forEach((device) => {
            console.log(device.name);
            let device_id = device.id;
            let device_name = device.name;
            let stations_select = document.getElementById("player_devices");
            let option = document.createElement("option");
            option.text = device_name;
            option.value = device_id;
            stations_select.add(option);
          });
        } else {
          $('#ad_psa_system_status_message').obWidget('error', res.msg);
        }
      });
    }, 200);
    setTimeout(() => {
      let player_devices = document.getElementById('player_devices');
      let company_name = document.getElementById('company_name');
      let company_phone = document.getElementById('company_phone');
      let company_street_address = document.getElementById('company_street_address');
      let company_city = document.getElementById('company_city');
      let company_state_prov = document.getElementById('state_prov');
      let company_zip_code = document.getElementById('company_zip_code');
      let company_country = document.getElementById('country');
      OB.API.post('obadpsadsystemmodule', 'get_company', {'id': id}, function(res) {
        let data = res.data;
        data.forEach(entry => {
          if (entry.id == id) {
            company_name.value = entry.name;
            company_phone.value = entry.phone;
            company_street_address.value = entry.street_addr;
            company_city.value = entry.city;
            company_state_prov.value = entry.state_prov;
            company_zip_code.value = entry.zip_code;
            company_country.value = entry.country;
          }
        });
      });
    }, 500);
  }

  // Opens the Campaign notes area.

  this.open_campaign_notes = function()
  {
    OB.UI.openModalWindow('modules/obadpsasystem/campaign_notes_modal.html');
    setTimeout(() => {
      // Display the last used notes if there in the local storage.
      let notes = OBModules.OBAdPSASystemModule.get_data('campaign_notes');
      if (notes != null) {
        document.getElementById('campaign_notes').value = notes;
      }
    }, 500);
  }

  // Saves the Campaign notes.

  this.save_campaign_notes = function()
  {
    let notes = document.getElementById('campaign_notes').value;
    OBModules.OBAdPSASystemModule.set_data('campaign_notes', notes);
    $('#ad_psa_system_modal_status_message').obWidget('success', 'Your Campaign notes has been saved!');
  }

  // Opens the Advertiser notes area.

  this.open_advertiser_notes = function()
  {
    OB.UI.openModalWindow('modules/obadpsasystem/advertiser_notes_modal.html');
    setTimeout(() => {
      // Display the last used notes if there in the local storage.
      let notes = OBModules.OBAdPSASystemModule.get_data('advertiser_notes');
      if (notes != null) {
        document.getElementById('advertiser_notes').value = notes;
      }
    }, 500);
  }

  // Saves the Advertiser notes.

  this.save_advertiser_notes = function()
  {
    let notes = document.getElementById('advertiser_notes').value;
    OBModules.OBAdPSASystemModule.set_data('advertiser_notes', notes);
    $('#ad_psa_system_modal_status_message').obWidget('success', 'Your Advertiser notes has been saved!');
  }

  // opens the screen to view campaigns.

  this.view_campaigns = function()
  {
    OB.UI.replaceMain('modules/obadpsasystem/view_campaigns.html');
    setTimeout(() => OBModules.OBAdPSASystemModule.load_campaigns, 800);
  }

  // Adds a campaign to the table of campaigns.
  this.add_campaign_to_table = function(id, start_date, start_time, stop_date, stop_time, buyer, device_names,
                                        name, advertiser)
  {
    let table = document.getElementById('campaign_table_body');
    let row = table.insertRow(table.rows.length);
    let cell1 = row.insertCell(0);
    let cell2 = row.insertCell(1);
    let cell3 = row.insertCell(2);
    let cell4 = row.insertCell(3);
    let cell5 = row.insertCell(4);
    let cell6 = row.insertCell(5);
    let cell7 = row.insertCell(6);
    let cell8 = row.insertCell(7);
    let cell9 = row.insertCell(8);

    cell1.innerHTML = htmlspecialchars(name);
    cell2.innerHTML = htmlspecialchars(`${start_date} ${start_time}`);
    cell3.innerHTML = htmlspecialchars(`${stop_date} ${stop_time}`);
    cell4.innerHTML = htmlspecialchars(buyer);
    cell5.innerHTML = htmlspecialchars(advertiser);
    cell6.innerHTML = htmlspecialchars(device_names);
    //cell7.innerHTML = `<button class="edit" onclick="OBModules.OBAdPSASystemModule.open_edit_company_modal(${id});">Edit</button>`;
    //cell8.innerHTML = `<button class="delete" onclick="OBModules.OBAdPSASystemModule.open_remove_company_modal(${id});">Remove</button>`;
  }

  // This should be runned anytime after
  // displaying a tts area.
  this.tts_voice_init = function()
  {
    OB.API.post('obadpsadsystemmodule', 'get_tts_voices', {}, function(res) {
      console.log(res);
      if (res.status) {
        let voices = res.data;
        OBModules.OBAdPSASystemModule.set_data('tts_voices', JSON.stringify(voices));
        voices.forEach((voice) => {
          OBModules.OBAdPSASystemModule.dropdown_add_item('voice', voice.voice_name);
        });
      } else {
        $('#ad_psa_system_tts_message').obWidget('error', res.msg);
      }
    });
  }

  // Updates voices from the local storage.
  this.update_voices = function ()
  {
    let voices_select = document.getElementById('voice');
    let gender = document.getElementById('gender').value;
    let language = document.getElementById('language').value;
    let voices = JSON.parse(OBModules.OBAdPSASystemModule.get_data('tts_voices'));
    let voices_list = [];
    let voice_subsets = [];
    voices_select.innerHTML = '';
    let language_sets = OBModules.OBAdPSASystemModule.get_data('language_sets').split(',');
    language_sets.forEach(language_set => {
    voices.forEach((voice) => {
        if (language_set == voice.subset && gender == voice.gender &&
          voices_list.includes(voice.voice_name) == false && language == voice.language) {
          voices_list.push(voice.voice_name);
        }
      });
    });
    console.log('voices_list', voices_list);
    voices_list.forEach(voice => {
      OBModules.OBAdPSASystemModule.dropdown_add_item('voice', voice);
    });
  }

  // Used to sort entrys into the campaigns table.

  this.sort_campaigns = function(sort_col)
  {
    localStorage.setItem('campaign_table_sortby', sort_col);
    OBModules.OBAdPSASystemModule.load_campainins();
  }

  // Gets the current campaigns from the server.

  this.load_campaigns = function()
  {
    let val = localStorage.getItem('campaign_table_sortby');
    if (val != null) {
      sort_col = val;
    } else {
      sort_col = 'name';
    }
    OB.API.post('obadpsadsystemmodule', 'get_campaigns', {'sort_col': sort_col}, function(res) {
      if (res.status) {
        res.data.forEach((campaign) => {
          console.log(campaign);
          OBModules.OBAdPSASystemModule.add_campaign_to_table(campaign.invoice_id,
                                                            campaign.start_date, campaign.start_time,
                                                            campaign.stop_date, campaign.stop_time,
                                                            campaign.buyer, campaign.device_names,
                                                            campaign.campaign_name, campaign.advertiser);
        });
      }
    });
  }

  // Opens the polly tts area view.
  this.view_polly_tts_area = function()
  {
    OB.UI.replaceMain('modules/obadpsasystem/tts_area.html');
    OBModules.OBAdPSASystemModule.tts_voice_init();
  }

  // Event handler for the selected langage being changed.
  this.selected_language_changed = function()
  {
    let language = document.getElementById('language').value;
    let language_sets = document.getElementById('language_sets');
    language_sets.innerHTML = '';
    switch (language) {
    case 'English':
      let options1 = ['Australian', 'British', 'Indian', 'US', 'Welsh'];
      options1.forEach((option) => {
        OBModules.OBAdPSASystemModule.dropdown_add_item('language_sets', option);
      });
      break;
    case 'Spanish':
      let options2 = ['European', 'Mexican', 'US Spanish'];
      options2.forEach((option) => {
        OBModules.OBAdPSASystemModule.dropdown_add_item('language_sets', option);
      });
      break;
    case 'French':
      let options3 = ['None', 'Canadian'];
      options3.forEach((option) => {
        OBModules.OBAdPSASystemModule.dropdown_add_item('language_sets', option);
      });
      break;
    case 'Portuguese':
      let options4 = ['Brazilian', 'European'];
      options4.forEach((option) => {
        OBModules.OBAdPSASystemModule.dropdown_add_item('language_sets', option);
      });
      break;
  }

  OBModules.OBAdPSASystemModule.update_voices();
  }

  // Event handler for the language subsets being changed.
  this.selected_language_subsets_changed = function()
  {
    let language_sets = $('#language_sets').val();
    OBModules.OBAdPSASystemModule.set_data('language_sets', language_sets);
    OBModules.OBAdPSASystemModule.update_voices();

  }


  // opens the screen to view companies.

  this.view_companies = function()
  {
    OBModules.OBAdPSASystemModule.get_page('view_companies.html');
    setTimeout(() => {
      OB.API.post('obadpsadsystemmodule', 'get_companines', {}, function(res) {
        if (res.status) {
          res.data.forEach((company) => {
            console.log(company);
            // id, name, email, street_addr, city, zip_code, phone, country
            OBModules.OBAdPSASystemModule.add_company_to_table(company.id, company.name,
                                                              company.street_addr, company.city,
                                                              company.zip_code, company.phone,
                                                              company.country);
          });
        }
      });
    }, 800);
  }

  // Opens the screen to add a new company to the system.

  this.add_new_company = function()
  {
    OB.UI.openModalWindow('modules/obadpsasystem/add_company.html');
    setTimeout(() => {
      OB.API.post('obadpsadsystemmodule', 'get_all_devices', {}, function(res) {
        //console.log(res.data);
        if (res.status) {
          let json_data = JSON.parse(res.data);
          //console.log(json_data);
          json_data.forEach((device) => {
            console.log(device.name);
            let device_id = device.id;
            let device_name = device.name;
            let stations_select = document.getElementById("player_devices");
            let option = document.createElement("option");
            option.text = device_name;
            option.value = device_id;
            stations_select.add(option);
          });
        } else {
          $('#ad_psa_system_modal_status_message').obWidget('error', res.msg);
          OBModules.OBAdPSASystemModule.disable_modal_btns(exclude='.edit');
        }
      });
    }, 500);
  }

  this.save_company = function(mode='new')
  {
    let company_name = document.getElementById('company_name').value;
    let company_phone = document.getElementById('company_phone').value;
    let company_street_address = document.getElementById('company_street_address').value;
    let company_city = document.getElementById('company_city').value;
    let company_state_prov = document.getElementById('state_prov').value;
    let company_zip_code = document.getElementById('company_zip_code').value;
    let company_country = document.getElementById('country').value;
    let player_id = document.getElementById('player_devices').value;
    let player_name = document.getElementById('player_devices').text;
    let create_buyer = document.getElementById('create_buyer').checked;
    let values = {'company_name': company_name, 'company_phone': company_phone,
                  'company_street_address': company_street_address, 'company_city': company_city,
                  'company_state_prov': company_state_prov, 'company_zip_code': company_zip_code,
                  'company_country': company_country, 'player_id': player_id, 'player_name': player_name,
                  'current_company_id': '', 'from_company': true};
    OB.API.post('obadpsadsystemmodule', 'verify_company', values, function(res) {
      console.log(res);
      if (res.status) {
        if (mode == 'new') {
          if (create_buyer) {
            OB.API.post('obadpsadsystemmodule', 'save_buyer', values, function(res) {
              if (res.status) {
                  $('#ad_psa_system_status_message').obWidget('success', 'Your buyer data has been saved!');
              } else {
                $('#ad_psa_system_status_message').obWidget('error', 'Your buyer data couldn\'t be saved been saved!');
              }
            });
          }
        }
          OB.API.post('obadpsadsystemmodule', 'save_company', values, function(res) {
            if (res.status) {
              $('#ad_psa_system_status_message').obWidget('success', 'Your company data has been saved!');
              OBModules.OBAdPSASystemModule.clear_companies_table_and_reload();
              OB.UI.closeModalWindow();
            } else {
              $('#ad_psa_system_status_message').obWidget('error', res.msg);
              OB.UI.closeModalWindow();
            }
          });
        } else if (mode == 'edit') {
          let current_company_id = OBModules.OBAdPSASystemModule.get_data('current_company_id');
          setTimeout(() => {
          let values = {'company_name': company_name, 'company_phone': company_phone,
                        'company_street_address': company_street_address, 'company_city': company_city,
                        'company_state_prov': company_state_prov, 'company_zip_code': company_zip_code,
                        'company_country': company_country, 'player_id': player_id, 'player_name': player_name,
                        'current_company_id': current_company_id};
          OB.API.post('obadpsadsystemmodule', 'update_company', values, function(res) {
            if (res.status) {
              $('#ad_psa_system_status_message').obWidget('success', 'Your company data has been saved!');
              OBModules.OBAdPSASystemModule.clear_companies_table_and_reload();
              OB.UI.closeModalWindow();
            } else {
              $('#ad_psa_system_status_message').obWidget('error', res.msg);
              OB.UI.closeModalWindow();
            }
          });
          }, 500);
      } else {
        $('#ad_psa_system_status_message').obWidget('error', res.msg);
        OB.UI.closeModalWindow();
      }
    });
  }

  this.add_new_buyer = function()
  {
    OB.UI.openModalWindow('modules/obadpsasystem/add_buyer.html');
    setTimeout(() => {

    }, 800);
  }

  // Adds buyer to the table of buyers.
  this.add_buyer_to_table = function(id, name, email, street_addr, city, zip_code, phone)
  {
    let table = document.getElementById('buyer_table_body');
    let row = table.insertRow(table.rows.length);
    let cell1 = row.insertCell(0);
    let cell2 = row.insertCell(1);
    let cell3 = row.insertCell(2);
    let cell4 = row.insertCell(3);
    let cell5 = row.insertCell(4);
    let cell6 = row.insertCell(5);
    let cell7 = row.insertCell(6);
    let cell8 = row.insertCell(7);

    cell1.innerHTML = htmlspecialchars(name);
    cell2.innerHTML = htmlspecialchars(phone);
    cell3.innerHTML = htmlspecialchars(email);
    cell4.innerHTML = htmlspecialchars(street_addr);
    cell5.innerHTML = htmlspecialchars(city);
    cell6.innerHTML = htmlspecialchars(zip_code);
    cell7.innerHTML = `<button class="edit" onclick="OBModules.OBAdPSASystemModule.open_edit_buyer_modal(${id});">Edit</button>`;
    cell8.innerHTML = `<button class="delete" onclick="OBModules.OBAdPSASystemModule.remove_buyer(${id});">Remove</button>`;
  }

  // Clears the buyer table to allow for reloading in place as needed.

  this.clear_buyer_table = function()
  {
    let table = document.getElementById('buyer_table_body');
    table.innerHTML = '';
  }

  this.load_buyers_and_add_to_view = function()
  {
    let val = localStorage.getItem('buyer_table_sortby');
    if (val != null) {
      sort_col = val;
    } else {
      sort_col = 'name';
    }
    OB.API.post('obadpsadsystemmodule', 'get_buyers', {'sort_col': sort_col}, function(res) {
      if (res.status) {
        res.data.forEach(buyer => {
          let id = buyer.id;
          let name = buyer.name;
          let phone = buyer.phone;
          let email = buyer.email;
          let street_addr = buyer.street_addr;
          let city = buyer.city;
          let state_prov = buyer.state_prov;
          let zip_code = buyer.zip_code;
          OBModules.OBAdPSASystemModule.add_buyer_to_table(id, name, email, street_addr, city, zip_code, phone);
        });
      }
    });
  }

  /* Shows all buyers, and allows editing info of a existing buyer.  */

  this.view_buyers = function()
  {
    OB.UI.replaceMain('modules/obadpsasystem/view_buyers.html')
    setTimeout(OBModules.OBAdPSASystemModule.load_buyers_and_add_to_view, 800);
  }

  // Handles resort, and reload of the buyers table.

  this.sort_buyers = function(sort_col)
  {
    localStorage.setItem('buyer_table_sortby', sort_col);
    OBModules.OBAdPSASystemModule.clear_buyer_table();
    OBModules.OBAdPSASystemModule.load_buyers_and_add_to_view();
  }

  // Clears the buyer fields as requested.
  // This is normally called after a new  buyer has been entered.

  this.clear_buyer_fields = function()
  {
    document.getElementById('buyer_name').value = '';
    document.getElementById('advertiser').value = '';
    document.getElementById('address').value = '';
    document.getElementById('city').value = '';
    document.getElementById('state_prov').value = '';
    document.getElementById('zip_code').value = '';
    document.getElementById('bill_to_email').value = '';
    document.getElementById('phone').value = '';
    document.getElementById('country').value = '';
  }

  this.save_buyer = function()
  {
    document.getElementById('save_btn').disabled = true;
    let id = OBModules.OBAdPSASystemModule.get_data('current_buyer');
    setTimeout(() => {
      this.can_save_buyer = false;
      let buyer_name = document.getElementById('buyer_name').value;
      let advertiser = document.getElementById('advertiser').value;
      let address = document.getElementById('address').value;
      let city = document.getElementById('city').value;
      let state_prov = document.getElementById('state_prov').value;
      let zip_code = document.getElementById('zip_code').value;
      let bill_to_email = document.getElementById('bill_to_email').value;
      let phone = document.getElementById('phone').value;
      let country = document.getElementById('country').value;
      let values  = {'name': buyer_name, 'advertiser': advertiser, 'street_addr': address,
                    'city': city, 'zip_code': zip_code, 'phone': phone, 'email': bill_to_email, 'country': country,
                    'state_prov': state_prov, 'id': id};
      OB.API.post('obadpsadsystemmodule', 'verify_buyer', values, function(res) {
        if (res.status == false) {
          $('#ad_psa_system_status_message').obWidget('error', res.msg);
        } else {
          if (id == null) {
            OB.API.post('obadpsadsystemmodule', 'save_buyer', values, function(res) {
              $('#ad_psa_system_status_message').obWidget(res.status ? 'success' : 'error', res.msg);
              if (res.status) {
                OB.UI.closeModalWindow();
                OBModules.OBAdPSASystemModule.clear_buyer_table();
                setTimeout(() => {
                  OBModules.OBAdPSASystemModule.load_buyers_and_add_to_view();
                }, 500);
                // setTimeout(() => {
                //   OB.UI.closeModalWindow();
                //   OBModules.OBAdPSASystemModule.clear_buyer_table();
                //   //OBModules.OBAdPSASystemModule.view_buyers();
                // }, 4000);
              }
            });
          } else {
            OB.API.post('obadpsadsystemmodule', 'update_buyer', values, function(res) {
              $('#ad_psa_system_status_message').obWidget(res.status ? 'success' : 'error', res.msg);
              OB.UI.closeModalWindow();
              OBModules.OBAdPSASystemModule.clear_buyer_table();
              setTimeout(() => {
                OBModules.OBAdPSASystemModule.load_buyers_and_add_to_view();
              }, 500);
            });
          }
        }
      });
    }, 500);
    OBModules.OBAdPSASystemModule.set_data('current_buyer', '');
  }

  // Fires when the select's value changes state, or province.
  // This will select the country based on the value of state_prov select.

  this.state_prov_dropdown_change = function()
  {
    let done = false;
    let selected_loc = document.getElementById('state_prov').value;
    let us_locs = ['AL', 'AK', 'AZ', 'AR', 'CA', 'CO',
                  'CT', 'DE', 'DC', 'FL', 'GA', 'HI',
                  'ID', 'IL', 'IN', 'IA', 'KS', 'KY',
                  'LA', 'ME', 'MD', 'MA', 'MI', 'MN',
                  'MN', 'MS', 'MO', 'MT', 'NE', 'NV',
                  'NH', 'NJ', 'NM', 'NY', 'NC', 'ND',
                  'OH', 'OK', 'OR', 'PA', 'RI', 'SC',
                  'SD', 'TN', 'TX', 'UT', 'VT', 'VA',
                  'WA', 'WV', 'WI', 'WY', 'AS', 'GU',
                  'MP', 'PR', 'VI'];
    let ca_locs = ['AB', 'BC', 'MB', 'NB', 'NL', 'NS', 'ON', 'PE', 'QC', 'SK',
                  'NT', 'NU', 'YT'];
    us_locs.forEach(loc => {
      if (loc == selected_loc) {
        done = true;
        OBModules.OBAdPSASystemModule.fill_dropdown('country', 'US');
      }
    });
    if (done == false) {
      ca_locs.forEach(loc => {
        if (loc == selected_loc) {
          done = true;
          OBModules.OBAdPSASystemModule.fill_dropdown('country', 'CA');
        }
      });
    }
  }

  // Fires when the select's value changes, and then remove the unneeded
  // values from the sate_prov select.

  this.county_dropdown_change = function()
  {
    let value = document.getElementById('country').value;
    switch (value) {
      case 'CA':
        document.getElementById('US_option_group').style.display = 'none';
        document.getElementById('US_Territories_option_group').style.display = 'none';
        document.getElementById('CA_option_group').style.display = 'block';
        document.getElementById('CA_Territories_option_group').style.display = 'block';
        break;
      case 'US':
        document.getElementById('US_option_group').style.display = 'block';
        document.getElementById('US_Territories_option_group').style.display = 'block';
        document.getElementById('CA_option_group').style.display = 'none';
        document.getElementById('CA_Territories_option_group').style.display = 'none';
        break;
    }
  }

  this.open_edit_buyer_modal = function(id)
  {
    OB.UI.openModalWindow('modules/obadpsasystem/edit_buyer.html');
    setTimeout(() => {
      OBModules.OBAdPSASystemModule.set_data('current_buyer', id);
      let buyer_name = document.getElementById('buyer_name');
      let advertiser = document.getElementById('advertiser');
      let address = document.getElementById('address');
      let city = document.getElementById('city');
      let state_prov = document.getElementById('state_prov');
      let zip_code = document.getElementById('zip_code');
      let bill_to_email = document.getElementById('bill_to_email');
      let phone = document.getElementById('phone');
      let country = document.getElementById('country');
      OB.API.post('obadpsadsystemmodule', 'get_buyer', {'id': id}, function(res) {
        console.log(res);
        if (res.status) {
          let fields = ['buyer_name', 'phone', 'email', 'street_addr', 'city', 'state_prov', 'zip_code', 'advertiser', 'country'];
          fields.forEach((feild) => {
            switch (feild) {
              case 'buyer_name':
                buyer_name.value = res.data.name;
                break;
              case 'advertiser':
                OBModules.OBAdPSASystemModule.fill_dropdown("advertiser", res.data.advertiser);
                break;
              case 'street_addr':
                address.value = res.data.street_addr;
                break;
              case 'city':
                city.value = res.data.city;
                break;
              case 'state_prov':
                OBModules.OBAdPSASystemModule.fill_dropdown("state_prov", res.data.state_prov);
                break;
              case 'zip_code':
                zip_code.value = res.data.zip_code;
                break;
              case 'email':
                bill_to_email.value = res.data.email;
                break;
              case 'phone':
                phone.value = res.data.phone;
                break;
              case 'country':
                OBModules.OBAdPSASystemModule.fill_dropdown("country", res.data.country);
                break;
            }
          });
        }
      });
    }, 500);
    document.getElementById('save_btn').addEventListener('click', (e) => {
      OBModules.OBAdPSASystemModule.save_buyer(id);
    });
  }

  this.edit_buyer = function(id)
  {
    // Placeholder
  }

  /* Closes the edit buyer modal. */

  this.close_edit_modal_btn_clicked = function()
  {
    OB.UI.closeModalWindow();
  }

  this.remove_buyer = function(id)
  {
    // TODO: Switch to OB.UI modal later.
    let remove = confirm('Are you sure you want to remove this buyer?');
    if (remove) {
      OB.API.post('obadpsadsystemmodule', 'remove_buyer', {'id': id}, function(res) {
        console.log(res);
        if (res.status) {
          $('#ad_psa_system_status_message').obWidget(res.status ? 'success' : 'error', 'buyer Removed!');
          // Clear, and reload the local display of the data.
          OBModules.OBAdPSASystemModule.clear_buyer_table();
          OBModules.OBAdPSASystemModule.load_buyers_and_add_to_view();
        }
      });
    }
  }

  this.save_settings = function()
  {
    //let aws_region_name = document.querySelector('#aws_region_name:selected');
    let aws_region_name = document.getElementById('aws_region_name').value;
    let aws_access_key_id = document.getElementById('aws_access_key_id').value;
    let aws_secret_access_key = document.getElementById('aws_secret_access_key').value;
    let stripe_publishable_key = document.getElementById('stripe_publishable_key').value;
    let stripe_secret_key = document.getElementById('stripe_secret_key').value;
    let currency = document.getElementById('currency').value;
    let morning_hourly_rate = document.getElementById('morning_hourly_rate').value;
    let midday_hourly_rate = document.getElementById('midday_hourly_rate').value;
    let drive_hourly_rate = document.getElementById('drive_hourly_rate').value;
    let ros_hourly_rate = document.getElementById('ros_hourly_rate').value;
    let country = document.getElementById('default_country').value;
    let default_language = OBModules.OBAdPSASystemModule.get_data('default_language');
    let default_language_subsets = OBModules.OBAdPSASystemModule.get_data('language_sets');
    let enabled_languages = OBModules.OBAdPSASystemModule.get_data('enabled_languages');
    console.log('Saving settings...');
    OB.API.post('obadpsadsystemmodule', 'update_setting', {'setting_name': 'aws_region_name', 'setting_value': aws_region_name}, function(res) {
      console.log(res);
      $('#ad_psa_system_settings_message').obWidget(res.status ? 'success' : 'error', res.msg);
    });
    OB.API.post('obadpsadsystemmodule', 'update_setting', {'setting_name': 'aws_access_key_id', 'setting_value': aws_access_key_id}, function(res) {
      console.log(res);
      $('#ad_psa_system_settings_message').obWidget(res.status ? 'success' : 'error', res.msg);
    });
    OB.API.post('obadpsadsystemmodule', 'update_setting', {'setting_name': 'aws_secret_access_key', 'setting_value': aws_secret_access_key}, function(res) {
      console.log(res);
      $('#ad_psa_system_settings_message').obWidget(res.status ? 'success' : 'error', res.msg);
    });
    OB.API.post('obadpsadsystemmodule', 'update_setting', {'setting_name': 'stripe_publishable_key', 'setting_value': stripe_publishable_key}, function(res) {
      console.log(res);
      $('#ad_psa_system_settings_message').obWidget(res.status ? 'success' : 'error', res.msg);
    });
    OB.API.post('obadpsadsystemmodule', 'update_setting', {'setting_name': 'stripe_secret_key', 'setting_value': stripe_secret_key}, function(res) {
      console.log(res);
      $('#ad_psa_system_settings_message').obWidget(res.status ? 'success' : 'error', res.msg);
    });
    OB.API.post('obadpsadsystemmodule', 'update_setting', {'setting_name': 'currency', 'setting_value': currency}, function(res) {
      console.log(res);
      $('#ad_psa_system_settings_message').obWidget(res.status ? 'success' : 'error', res.msg);
    });
    OB.API.post('obadpsadsystemmodule', 'update_setting', {'setting_name': 'morning_hourly_rate', 'setting_value': morning_hourly_rate}, function(res) {
      console.log(res);
      $('#ad_psa_system_settings_message').obWidget(res.status ? 'success' : 'error', res.msg);
    });
    OB.API.post('obadpsadsystemmodule', 'update_setting', {'setting_name': 'midday_hourly_rate', 'setting_value': midday_hourly_rate}, function(res) {
      console.log(res);
      $('#ad_psa_system_settings_message').obWidget(res.status ? 'success' : 'error', res.msg);
    });
    OB.API.post('obadpsadsystemmodule', 'update_setting', {'setting_name': 'drive_hourly_rate', 'setting_value': drive_hourly_rate}, function(res) {
      console.log(res);
      $('#ad_psa_system_settings_message').obWidget(res.status ? 'success' : 'error', res.msg);
    });
    OB.API.post('obadpsadsystemmodule', 'update_setting', {'setting_name': 'ros_hourly_rate', 'setting_value': ros_hourly_rate}, function(res) {
      console.log(res);
      $('#ad_psa_system_settings_message').obWidget(res.status ? 'success' : 'error', res.msg);
    });
    OB.API.post('obadpsadsystemmodule', 'update_setting', {'setting_name': 'default_country', 'setting_value': country}, function(res) {
      console.log(res);
      $('#ad_psa_system_settings_message').obWidget(res.status ? 'success' : 'error', res.msg);
    });
    OB.API.post('obadpsadsystemmodule', 'update_setting', {'setting_name': 'default_language', 'setting_value': default_language}, function(res) {
      console.log(res);
      $('#ad_psa_system_settings_message').obWidget(res.status ? 'success' : 'error', res.msg);
    });
    OB.API.post('obadpsadsystemmodule', 'update_setting', {'setting_name': 'default_language_sets', 'setting_value': default_language_subsets}, function(res) {
      console.log(res);
      $('#ad_psa_system_settings_message').obWidget(res.status ? 'success' : 'error', res.msg);
    });
    OB.API.post('obadpsadsystemmodule', 'update_setting', {'setting_name': 'enabled_languages', 'setting_value': enabled_languages}, function(res) {
      console.log(res);
      $('#ad_psa_system_settings_message').obWidget(res.status ? 'success' : 'error', res.msg);
    });
  }

  // Opens the add company modal in the settings.

  this.open_add_company_to_player_modal_btn_clicked = function()
  {
    OB.UI.openModalWindow('modules/obadpsasystem/add_company.html');
    // Reload the settings after modal is opened.
    //OBModules.OBAdPSASystemModule.load_settings();
    OB.API.post('obadpsadsystemmodule', 'get_all_devices', {}, function(res) {
      //console.log(res.data);
      let json_data = JSON.parse(res.data);
      //console.log(json_data);
      json_data.forEach((device) => {
        console.log(device.name);
        let device_id = device.id;
        let device_name = device.name;
        let stations_select = document.getElementById("player_devices");
        let option = document.createElement("option");
        option.text = device_name;
        option.value = device_id;
        stations_select.add(option);
      });
    });
  }

  this.close_add_company_to_player_modal_btn_clicked = function()
  {
    OB.UI.closeModalWindow();
  }

  // Loads the currently enabled languages into the
  // checkboxes on the settings page.

  this.load_enabled_langauges = function()
  {
    OB.API.post('obadpsadsystemmodule', 'get_setting', {'setting_name': 'enabled_languages'}, function(res) {
      if (res.status) {
        let langauges = res.data.split(',');
        langauges.forEach(langauge => {
          switch (langauge) {
            case 'english':
              document.getElementById('English_checkbox').checked = true;
              break;
            case 'arabic':
              document.getElementById('Arabic_checkbox').checked = true;
              break;
            case 'chinese-mandarin':
              document.getElementById('Chinese_Mandarin_checkbox').checked = true;
              break;
            case 'dutch':
              document.getElementById('Dutch_checkbox').checked = true;
              break;
            case 'french':
              document.getElementById('French_checkbox').checked = true;
              break;
            case 'german':
              document.getElementById('German_checkbox').checked = true;
              break;
            case 'italian':
              document.getElementById('Italian_checkbox').checked = true;
              break;
            case 'portuguese':
              document.getElementById('Portuguese_checkbox').checked = true;
              break;
            case 'spanish':
              document.getElementById('Spanish_checkbox').checked = true;
              break;
            }
        });
      }
    });
  }

  // Loads and display settings.

  this.load_settings = function() {
    console.log('loading settings...');
    OBModules.OBAdPSASystemModule.load_enabled_langauges();
    let settings_name = ['company_name', 'currency', 'aws_region_name', 'aws_access_key_id', 'aws_secret_access_key', 'stripe_publishable_key', 'stripe_secret_key',
                        'company_phone', 'company_street_address', 'company_state_prov', 'company_city',
                        'company_zip_code', 'company_country', 'morning_hourly_rate',
                        'midday_hourly_rate', 'drive_hourly_rate', 'ros_hourly_rate',
                        'default_country', 'default_language', 'default_language', 'default_language_sets'];

    for (var i = 0; i < settings_name.length; i++) {
      let dom_ele = settings_name[i];
      OB.API.post('obadpsadsystemmodule', 'get_setting', {'setting_name': settings_name[i]}, function(res) {
        // Added to catch error if the add conpany modal is closed.
        try {
          if (dom_ele == 'default_language_sets') {
            dom_ele = 'language_sets';
            let language_set_items = res.data.split(',');
            console.log('language_set_items', language_set_items);
            OBModules.OBAdPSASystemModule.add_dropdown_items(dom_ele, language_set_items);
            language_set_items.forEach(item => {
              OBModules.OBAdPSASystemModule.fill_dropdown(dom_ele, item);
            });
          } else {
            document.getElementById(dom_ele).value = res.data;
          }
          if (dom_ele == 'default_country' || dom_ele == 'default_language' || dom_ele == 'default_language') {
            if (res.data != '') {
              document.getElementById(dom_ele).value = res.data;
              OBModules.OBAdPSASystemModule.fill_dropdown(dom_ele, res.data);
            }
          }
        } catch (e) {
          console.log(`Not displaying settings ${dom_ele}, due to the add conpany modal being closed.`);
        }
      });
    }
    // Handles first load of language sets.
    OBModules.OBAdPSASystemModule.language_selected();
  }

  // Displays Module settings

  this.view_settings = function()
  {
      OBModules.OBAdPSASystemModule.get_page('settings.html');
      setTimeout(OBModules.OBAdPSASystemModule.load_settings, 2000);
  }

  // Disable, or Enable text fields on the page.

  this.change_page_items_state = function(state) {
    let inputs = layout_main.getElementsByTagName('input');
    let items = Array.from(inputs);
    let selects = layout_main.getElementsByTagName('select');
    if (selects != null) {
      for (var i = 0; i < selects.length; i++) {
        items.push(selects[i]);
      }
    }
    console.log('items', items);
    if (state == false) {
      for (var i = 0; i < items.length; i++) {
        items[i].disabled = state;
      }
    } else {
      for (var i = 0; i < items.length; i++) {
        items[i].setAttribute('disable', '');
      }
    }
  }

  // adds table entry for a invoice.

  this.add_invoice_table_item = function (invoice_id, devices)
  {
    let table = document.getElementById('invoice_table');
    let row = table.insertRow(table.rows.length);
    let cell1 = row.insertCell(0);
    let cell2 = row.insertCell(1);
    let cell3 = row.insertCell(2);

    cell1.innerHTML = htmlspecialchars(invoice_id);
    cell2.innerHTML = htmlspecialchars(devices);
    OB.API.post('obadpsadsystemmodule', 'permissons_check', {'name': 'ad_psa_system_create_invoices'}, function(res) {
      if (res.status) {
        cell3.innerHTML = `<a class="" href="modules/obadpsasystem/invoice/?id=${invoice_id}&mode=1" target="_blank">View Invoice</a>`;
      } else {
        cell3.innerHTML = `<a class="" href="modules/obadpsasystem/invoice/?id=${invoice_id}" target="_blank">View Invoice</a>`;
      }
    });
  }

  // opens page of sent invoices.

  this.view_invoices = function()
  {
    OB.UI.replaceMain('modules/obadpsasystem/view_invoices.html');
    let val = localStorage.getItem('invoice_table_sortby');
    if (val != null) {
      sort_col = val;
    } else {
      sort_col = 'id';
    }
    OB.API.post('obadpsadsystemmodule', 'get_invoices', {'sort_col': sort_col}, function(res) {
      if (res.status) {
        console.log(res);
        if (res.data != null) {
          let json_data = JSON.parse(res.data);
          let invoices = json_data;
          for (var i = 0; i < invoices.length; i++) {
            OBModules.OBAdPSASystemModule.add_invoice_table_item(invoices[i].invoice_id, invoices[i].device_names);
          }
        } else {
            $('#ad_psa_system_status_message').obWidget('success', 'No invoices found!');
        }
      } else {
        //$('#ad_psa_system_status_message').obWidget('error', 'A request to get the invoices has failed! If this continues, please contact a server admin.');
        $('#layout_main').html('<h3>No invoices found!</h3><p>Since there isn\'t any invoices you need to create first.</p><button onclick="OBModules.OBAdPSASystemModule.start_page();">Create invoice...?</button>');
      }
    });
  }

  // Handles sort request for invoices.

  this.sort_invoices = function(sort_col)
  {
    let val = localStorage.setItem('invoice_table_sortby', sort_col);
    OBModules.OBAdPSASystemModule.view_invoices();
  }

  this.start_page_data_init = function()
  {
    let layout_main = document.getElementById('layout_main_container');
    layout_main.addEventListener('mouseleave', e => {
        OBModules.OBAdPSASystemModule.disable_checks = true;
      e.preventDefault();
    });
    layout_main.addEventListener('mouseenter', e => {
      OBModules.OBAdPSASystemModule.disable_checks = false;
      e.preventDefault();
    });
    //setTimeout(OBModules.OBAdPSASystemModule.load_buyers, 100);
    setTimeout(() => {
      document.getElementById('start_date').value = OBModules.OBAdPSASystemModule.get_date(now=true);
      document.getElementById('stop_date').value = OBModules.OBAdPSASystemModule.get_date(now=false);
      $("#start_date").datepicker();
      $("#stop_date").datepicker();
    }, 500);
    OB.API.post('obadpsadsystemmodule', 'get_all_devices', {}, function(res) {
      //console.log(res.data);
      if (res.status) {
        let json_data = JSON.parse(res.data);
        //console.log(json_data);
        json_data.forEach((device) => {
          console.log(device.name);
          let device_id = device.id;
          let device_name = device.name;
          let stations_select = document.getElementById("stations");
          let option = document.createElement("option");
          option.text = device_name;
          option.value = device_id;
          stations_select.add(option);
        });
      } else {
        $('#ad_psa_system_status_message').obWidget('error', res.msg);
        OBModules.OBAdPSASystemModule.disable_all_btns();
      }
    });
    OB.API.post('obadpsadsystemmodule', 'get_advertisers', {}, function(res) {
      res.data.forEach((advertiser) => {
        let advertiser_id = advertiser.id;
        let advertiser_name = advertiser.name;
        let advertiser_select = document.getElementById("advertiser");
        let option = document.createElement("option");
        option.text = advertiser_name;
        option.value = advertiser_id;
        advertiser_select.add(option);
      });
    });
  }

  // Loads the current top of wizard screen values.

  this.load_current_top_screen_values = function ()
  {
    document.getElementById('current_advertiser').innerHTML = OBModules.OBAdPSASystemModule.get_data('advertiser');
    document.getElementById('current_buyer').innerHTML = OBModules.OBAdPSASystemModule.get_data('buyer');
  }

  // Returns date for today, or 3o days after today.

  this.get_date = function (now=true)
  {
    let current_date = new Date();
    if (now) {
      return current_date.toLocaleDateString();
    } else {
      current_date.setMonth(current_date.getMonth() + 1);
      return current_date.toLocaleDateString();
    }
  }

  this.media_data_init = function()
  {
    //setTimeout(OBModules.OBAdPSASystemModule.load_buyers, 100);
    //setTimeout(OBModules.OBAdPSASystemModule.select_last_buyer, 800);
    setTimeout(() => {
      OBModules.OBAdPSASystemModule.load_current_top_screen_values();
    }, 800);
    OBModules.OBAdPSASystemModule.message_text_onload();
  }

  // Opens the start page of the Wizard. This should load on module startup.

  this.start_page = function()
 	{
    OBModules.OBAdPSASystemModule.get_page('start.html', bypass_feild_check=true);
    OBModules.OBAdPSASystemModule.start_page_data_init();
    setTimeout(OBModules.OBAdPSASystemModule.get_page_fields, 2000);
    //OBModules.OBAdPSASystemModule.change_page_items_state(false);
    //$('#layout_main').html(OBModules.OBAdPSASystemModule.html);
 	}

  this.new_buyer_clicked = function(e)
  {
    this.change_page_items_state(false);
    OBModules.OBAdPSASystemModule.enable_next_btn();
    OBModules.OBAdPSASystemModule.clear_buyer_fields();
  }

  // Handles clearing of buyer fields.

  this.clear_buyer_fields = function()
  {
    document.getElementById('advertiser').value = '';
    document.getElementById('address').value = '';
    document.getElementById('city').value = '';
    document.getElementById('state_prov').value = '';
    document.getElementById('zip_code').value = '';
    document.getElementById('bill_to_email').value = '';
    OBModules.OBAdPSASystemModule.fill_dropdown('country', '', false);
  }

  // Handles fetching of all current buyers and adding the dropdown menu.

  this.load_buyers = function()
  {
    OB.API.post('obadpsadsystemmodule', 'get_buyers', {'sort_col': 'name'}, function(res) {
      console.log(res.data);
      res.data.forEach((buyer) => {
        let buyer_id = buyer.id;
        let buyer_name = buyer.name;
        let buyers_select = document.getElementById("select_existing_buyer");
        let option = document.createElement("option");
        option.text = buyer_name;
        option.value = buyer_id;
        buyers_select.add(option);
      });
    });
  }

  // Fills / selects the value in the dropdown provided.

  this.fill_dropdown = function (dropdown, dropdown_value, disable_after_fill = false) {
    let dropdown_items = document.getElementById(dropdown);
    dropdown_items.forEach((item) => {
      if (item.value == dropdown_value) {
        item.selected = true;
      }
    });
    if (disable_after_fill) {
      dropdown_items.disabled = true;
    }
  }

  this.add_dropdown_items = function (dropdown, items, disable_after_fill = false) {
    let dropdown_ele = document.getElementById(dropdown);
    items.forEach((item) => {
      let option_ele = document.createElement('option');
      option_ele.value = item;
      option_ele.innerHTML = item;
      dropdown_ele.appendChild(option_ele);
    });
    if (disable_after_fill) {
      dropdown_items.disabled = true;
    }
  }

  // Fills in billing info in for the selected buyer.

  this.fill_buyer_fields = function()
  {
    let buyer_id = document.getElementById("select_existing_buyer").value;
    OBModules.OBAdPSASystemModule.set_data('buyer_id', buyer_id);
    OB.API.post('obadpsadsystemmodule', 'get_buyer', {'id': buyer_id}, function(res) {
      console.log(res.data);
      if (res.status) {
        let data = res.data;
        document.getElementById("advertiser").value = data.advertiser;
        document.getElementById("buyer").value = data.name;
        document.getElementById("address").value = data.street_addr;
        document.getElementById("city").value = data.city;
        document.getElementById("zip_code").value = data.zip_code;
        document.getElementById("bill_to_email").value = data.email;
        OBModules.OBAdPSASystemModule.fill_dropdown("state_prov", data.state_prov);
        OBModules.OBAdPSASystemModule.fill_dropdown("country", data.country);
      }
    });
    // Act like the user clicked the new buyer button. This enables the feilds,
    // and the wizzard to be able to move forward.
    OBModules.OBAdPSASystemModule.new_buyer_clicked(null);
  }

  this.select_last_buyer = function()
  {
    let buyer_id = OBModules.OBAdPSASystemModule.get_data('buyer_id');
    OBModules.OBAdPSASystemModule.fill_dropdown('select_existing_buyer', buyer_id, true);
  }

  // for switches to done page. After demo should be switched to handle actually payment.

  this.payment_btn_clicked = function()
  {
    OBModules.OBAdPSASystemModule.get_page('done.html');
  }

  // Handles opening the modal used for uploading the logo.

  this.open_logo_modal_btn_clicked = function()
  {
    OB.UI.openModalWindow('modules/obadpsasystem/logo_upload.html');
  }

  // Handles the save logo button being clicked.
  // it then uploads the logo.

  this.save_logo_btn_clicked = function()
  {
    let logo_file = document.getElementById('logo_file');
    let file_path = logo_file.value;
    // Based on the code from https://stackoverflow.com/questions/423376/how-to-get-the-file-name-from-a-full-path-using-javascript
    let filename = file_path.replace(/^.*[\\\/]/, '')
    OB.API.post('obadpsadsystemmodule', 'save_station_logo', {'data': $('#logo_file')[0].files[0]}, function(res) {
      console.log(res);
      $('#ad_psa_system_logo_upload_status_message').obWidget(res.status ? 'success' : 'error', res.msg);
    });
  }

  this.media_upload = function()
  {
    OB.UI.openModalWindow('modules/obadpsasystem/media_loader.html');
    $.ajax({
      url: '/upload.php',
      type: 'POST',
      data: $('#audio_file')[0].files[0],
      cache: false,
      contentType: false,
      processData: false,
      complete : OBModules.OBAdPSASystemModule.media_upload_done
    });
  }

  this.media_upload_done = function(xhr)
  {
    OB.UI.closeModalWindow();
    var response = $.parseJSON(xhr.responseText);
    OBModules.OBAdPSASystemModule.media = response;
    let media = OBModules.OBAdPSASystemModule.media;
    let id = media['id'];
    let file_id = media['file_id'];
    let file_key = media['file_key'];
    let creative = document.getElementById('creative').value;
    let ad_id = document.getElementById('ad_id').value;
    let message_type = document.getElementById('message_type').value;
    let country = document.getElementById('country').value;
    let language = document.getElementById('language').value;
    let has_ad_id = document.getElementById('has_ad_id').checked;
    if (has_ad_id != false) {
      ad_id = document.getElementById('ad_id').value;
    }
    if (has_ad_id) {
      has_ad_id = false;
    }

    let values = {'file_key': file_key, 'file_id': file_id, 'id': id, 'spot_name': creative,
                  'ad_id': ad_id, 'message_type': message_type, 'has_ad_id': has_ad_id, 'country': country, 'language': language};
    OB.API.post('obadpsadsystemmodule', 'save_media', values, function(res) {
      console.log(res);
      // Check if media was uploaded without error, else display a error.
      if (res.status) {
        $('#ad_psa_system_status_message').obWidget('success', "Your AD/PSA is uploaded. Do not exit the wizard!");
        // Reload the media sidebar after the upload is saved.
        OB.Sidebar.mediaSearch();
        OBModules.OBAdPSASystemModule.enable_next_btn();
      } else {
        $('#ad_psa_system_status_message').obWidget('error', "Your AD/PSA couldn't be uploaded. Please contact a server admin.");
      }
    });
  }

  this.media_check = function()
  {
    let files = document.getElementById('audio_file').files;
    for (var i = 0; i < files.length; i++) {
      console.log('file:', files[i].name, 'size:', files[i].size);
    }
  }

  this.selected_audio_type = function()
  {
    let audio_type = document.getElementById('audio_type').value;
    let audio_file_upload_area = document.getElementById('audio_file_upload_area');
    let polly_tts_area = document.getElementById('polly_tts_area');
    if (audio_type == 'aws_tts') {
        audio_file_upload_area.style.display = 'none';
        polly_tts_area.style.display = 'block';
    } else {
      audio_file_upload_area.style.display = 'block';
      polly_tts_area.style.display = 'none';
    }
  }

  // Requests tts audio from AWS Polly.

  this.request_tts_audio = function()
  {
    let voice = document.getElementById('voice').value;
    let voice_name = voice.replace(/ \(Male\)| \(Female\)/g, '');
    let last_voice = OBModules.OBAdPSASystemModule.get_data('last_voice');
    let message_text = document.getElementById('message_text').value;
    let last_message_text = OBModules.OBAdPSASystemModule.get_data('last_aws_polly_text');
    let tts_speed = document.getElementById('tts_speed').value;
    let last_tts_speed = OBModules.OBAdPSASystemModule.get_data('last_tts_speed');
    let bed_music = document.getElementById('bed_music').value;
    let submit_audio_tts_btn = document.getElementById('submit_audio_tts_btn');
    if (bed_music == "") { bed_music = null; }
    let bed_music_vol = document.getElementById('bed_music_vol').value;

    if (last_message_text != message_text || last_tts_speed != tts_speed || last_voice != voice) {
      OB.API.post('obadpsadsystemmodule', 'request_tts_audio', {
        'voice': voice_name,
        'text': message_text,
        'speed': tts_speed,
        'bed_music': bed_music,
        'bed_music_vol': bed_music_vol
      }, function(res) {
        console.log(res);
        if (res.status == false) {
          $('#ad_psa_system_status_message').obWidget('error', res.msg);
          submit_audio_tts_btn.disabled = true;
        } else {
          submit_audio_tts_btn.disabled = false;
          let data = JSON.parse(res.data);
          let file_path = data.file_path;
          console.log(data);
          OBModules.OBAdPSASystemModule.set_data('last_aws_polly_text', message_text);
          OBModules.OBAdPSASystemModule.set_data('last_aws_polly_audio_filename', file_path);
          OBModules.OBAdPSASystemModule.set_data('last_tts_speed', tts_speed);
          OBModules.OBAdPSASystemModule.play_tts(mode='load');
        }
      });
    } else {
      OBModules.OBAdPSASystemModule.play_tts(mode='play');
    }
  }

  // Plays tts audio from AWS Polly.

  this.play_tts = function(mode='play')
  {
    let file_name = OBModules.OBAdPSASystemModule.get_data('last_aws_polly_audio_filename');
    let submit_audio_tts_btn = document.getElementById('submit_audio_tts_btn');
    let audio_player_area = document.getElementById('sidebar_player_draghere');
    let url = 'modules/obadpsasystem/audio.php?filename=' + file_name;
    if (mode == 'load') {
      audio_player_area.innerHTML = `<audio id="audio_player" controls autoplay><source src="${url}" type="audio/wav"></audio>`;
    }
    let audio_player = document.getElementById('audio_player');
    audio_player.play();
  }

  // Stops playback of AWS Polly audio, if any message is playing.

  this.stop_tts = function()
  {
    let audio_player = document.getElementById('audio_player');
    audio_player.pause();
  }

  // Uploads final audio mix to the server.

  this.submit_audio_tts = function()
  {
    let last_aws_polly_audio_filename = OBModules.OBAdPSASystemModule.get_data('last_aws_polly_audio_filename');
    let creative = document.getElementById('creative').value;
    let message_type = document.getElementById('message_type').value;
    let tts_start_btn = document.getElementById('tts_start_btn');
    let tts_stop_btn = document.getElementById('tts_stop_btn');
    let has_ad_id = document.getElementById('has_ad_id').checked;
    let standalone = document.getElementById('standalone').value;
    let county = document.getElementById('country').value;
    let ad_id = '';

    console.log('has_ad_id', has_ad_id);

    // Handle TTS standlone area.
    if (has_ad_id != false) {
      ad_id = document.getElementById('ad_id').value;
    }
    OB.API.post('obadpsadsystemmodule', 'submit_tts_audio', {
      'audio_file': last_aws_polly_audio_filename,
      'creative': creative,
      'message_type': message_type,
      'ad_id': ad_id,
      'has_ad_id': has_ad_id,
      'standalone': standalone,
      'county': county
    }, function(res) {
      console.log(res);
      if (res.status == true) {
        $('#ad_psa_system_status_message').obWidget('success', res.msg);
        tts_start_btn.disabled = true;
        tts_stop_btn.disabled = true;
        if (standalone) {
          OBModules.OBAdPSASystemModule.enable_next_btn();
        }
        let response = JSON.parse(res.data);
        OBModules.OBAdPSASystemModule.media = response;
        let media = OBModules.OBAdPSASystemModule.media;
        //let id = media['id'];
        //OBModules.OBAdPSASystemModule.set_data('current_media_id', id);
        let file_id = media['file_id'];
        let id = media['file_id'];
        let file_key = media['file_key'];
      } else {
        //alert("Your AD/PSA couldn't be uploaded! Please contact a system admin.");
        //$('#ad_psa_system_status_message').obWidget('error', "Your AD/PSA couldn't be uploaded! Please contact a system admin.");
        $('#ad_psa_system_status_message').obWidget('error', res.msg);
      }
    });
  }

  // Adds a event listener for handleing hiding/displaying the bed music volume control
  // if no track selected.

  this.bed_music_handling = function()
  {
    let bed_music = document.getElementById('bed_music').value;
    let tts_vol_area = document.getElementById('tts_vol_area');
    if (bed_music == '') {
      tts_vol_area.style.display = 'none';
    } else {
      tts_vol_area.style.display = 'block';
    }
  }

  // Adds a event listener for text changes for the tts message
  // textbox on page load.

  this.message_text_onload = function()
  {
    this.character_count = document.getElementById('character_count');
    let message_text_box = document.getElementById('message_text');
    message_text_box.addEventListener('keydown', OBModules.OBAdPSASystemModule.message_text_on_keydown);
  }

  this.message_text_on_keydown = function(e)
  {
    let count = e.target.value.length;
    OBModules.OBAdPSASystemModule.character_count.innerHTML = count + ' chars';
    if (count >= 3000) {
      if (e.key == 'Backspace' || e.key == 'Delete') {
      } else {
        e.preventDefault();
      }
    }
  }

  // this.regex_test = function(regex, string)
  // {
  //     let re = new RegExp(regex);
  //     return re.test(string);
  // }
  //
  // // checks if data is valid.
  // // returns true if valid.
  //
  // this.check_data = function()
  // {
  //   for (var i = 0; i < OBModules.OBAdPSASystemModule.fields.length; i++) {
  //     let name = OBModules.OBAdPSASystemModule.fields[i].name;
  //     let value = OBModules.OBAdPSASystemModule.fields[i].value;
  //     switch (name) {
  //       case "":
  //         break;
  //       default:
  //     }
  //   }
  //   return true;
  // }

  // saves data to localStorage for use later in the wizard.

  this.save_data = async function(current_page, next_page)
  {
    OBModules.OBAdPSASystemModule.get_page_fields();
    // If one, or more fields aren't vaild, stop move to next page.
    if (OBModules.OBAdPSASystemModule.fields_check() == false) {
      return;
    } else {
      let tmp_array = [];
      if (current_page == 'start.html') {
        let contract_number = document.getElementById('contract_number').value;
        OBModules.OBAdPSASystemModule.set_data('contract_number', contract_number);
        let revenue_type = document.getElementById('revenue_type').value;
        OBModules.OBAdPSASystemModule.set_data('revenue_type', revenue_type);
        let advertiser = document.getElementById('advertiser').value;
        OBModules.OBAdPSASystemModule.set_data('advertiser', advertiser);
        let buyer = document.getElementById('buyer').value;
        OBModules.OBAdPSASystemModule.set_data('buyer', buyer);
        let start_date = document.getElementById('start_date').value;
        OBModules.OBAdPSASystemModule.set_data('start_date', start_date);
        let stop_date = document.getElementById('stop_date').value;
        OBModules.OBAdPSASystemModule.set_data('stop_date', stop_date);
        let campaign_name = document.getElementById('campaign_name').value;
        OBModules.OBAdPSASystemModule.set_data('campaign_name', campaign_name);
        let address = document.getElementById('address').value;
        OBModules.OBAdPSASystemModule.set_data('address', address);
        let city = document.getElementById('city').value;
        OBModules.OBAdPSASystemModule.set_data('city', city);
        let state_prov = document.getElementById('state_prov').value;
        OBModules.OBAdPSASystemModule.set_data('state_prov', state_prov);
        let zip_code = document.getElementById('zip_code').value;
        OBModules.OBAdPSASystemModule.set_data('zip_code', zip_code);
        let bill_to_email = document.getElementById('bill_to_email').value;
        OBModules.OBAdPSASystemModule.set_data('bill_to_email', bill_to_email);
        let country = document.getElementById('country').value;
        OBModules.OBAdPSASystemModule.set_data('country', country);
        let sales_rep = document.getElementById('sales_rep').value;
        OBModules.OBAdPSASystemModule.set_data('sales_rep', sales_rep);
        let stations = $('#stations').val();
        let stations_str = '';
        if (stations.length > 1) {
          stations.forEach((station) => {
            station_str = station + ',';
            let tmp_str = stations_str.concat(stations_str, station_str);
            stations_str = stations_str + tmp_str;
          });
        } else {
          stations_str = stations;
        }
        OBModules.OBAdPSASystemModule.set_data('stations', stations_str);
        let time_slots_values = [];
        let time_slots = Array.from(document.getElementById('time_slots').selectedOptions);
        time_slots.forEach((time_slot) => {
          time_slots_values.push(time_slot.value);
        });
        OBModules.OBAdPSASystemModule.set_data('time_slots', time_slots_values.join(','));
      } else if (current_page == 'media_info.html') {
        let creative = document.getElementById('creative').value;
        OBModules.OBAdPSASystemModule.set_data('creative', creative);
        let has_ad_id = document.getElementById('has_ad_id').checked;
        console.log('has_ad_id', has_ad_id);
        OBModules.OBAdPSASystemModule.set_data('has_ad_id', has_ad_id);
        if (has_ad_id == false) {
          let ad_id = document.getElementById('ad_id').value;
          OBModules.OBAdPSASystemModule.set_data('ad_id', ad_id);
        } else {
          // Handle not requiring a Ad-ID.
          OBModules.OBAdPSASystemModule.set_data('ad_id', "NA");
          document.getElementById('ad_id').value = 'NA';
        }
        let tmp_media_type = document.getElementById('audio_type').value;
        let media_type = tmp_media_type = 'upload' ? 1 : 2;
        OBModules.OBAdPSASystemModule.set_data('media_type', media_type);
      }
      //let tmp_array = {};
      // for (var i = 0; i < OBModules.OBAdPSASystemModule.fields.length; i++) {
      //   let name = OBModules.OBAdPSASystemModule.fields[i].name;
      //   console.log(name);
      //   let value = OBModules.OBAdPSASystemModule.fields[i].value;
      //   tmp_array.push({"name": name, "value": value});
      //   //tmp_object.push({name: value});
      //   //tmp_array.push({name: value});

      // }
      //OBModules.OBAdPSASystemModule.check_data();
      let json = JSON.stringify(tmp_array);
      //console.log('json', json);
      switch (current_page) {
        case "start.html":
          OBModules.OBAdPSASystemModule.set_data('start_page', json);
          OBModules.OBAdPSASystemModule.bill_to_fields = tmp_array;
          break;
        case "broadcast_data.html":
          OBModules.OBAdPSASystemModule.set_data('broadcast_data', json);
          OBModules.OBAdPSASystemModule.broadcast_data = tmp_array;
          break;
      }
      OBModules.OBAdPSASystemModule.get_page(next_page);
    }
  }

  // method to submit data to the server for saving of a new invoice.

  this.submit_invoice = function()
  {
    let campaign_name = OBModules.OBAdPSASystemModule.get_data('campaign_name');
    let advertiser = OBModules.OBAdPSASystemModule.get_data('advertiser');
    let address = OBModules.OBAdPSASystemModule.get_data('address');
    let city = OBModules.OBAdPSASystemModule.set_data('city');
    let state_prov = OBModules.OBAdPSASystemModule.get_data('state_prov');
    let zip_code = OBModules.OBAdPSASystemModule.get_data('zip_code');
    let bill_to_email = OBModules.OBAdPSASystemModule.get_data('bill_to_email');
    let stations = OBModules.OBAdPSASystemModule.get_data('stations');
    let creative = OBModules.OBAdPSASystemModule.get_data('creative');
    let ad_id = OBModules.OBAdPSASystemModule.get_data('ad_id');
    let time_slots = OBModules.OBAdPSASystemModule.get_data('time_slots');
    let country = OBModules.OBAdPSASystemModule.get_data('country');
    let revenue_type = OBModules.OBAdPSASystemModule.get_data('revenue_type');
    let start_date = OBModules.OBAdPSASystemModule.get_data('start_date');
    let stop_date = OBModules.OBAdPSASystemModule.get_data('stop_date');
    let buyer = OBModules.OBAdPSASystemModule.get_data('buyer');
    let contract_number = OBModules.OBAdPSASystemModule.get_data('contract_number');
    let media_id = OBModules.OBAdPSASystemModule.get_data('current_media_id');
    let campaign_notes = OBModules.OBAdPSASystemModule.get_data('campaign_notes');
    let media_type = OBModules.OBAdPSASystemModule.get_data('media_type');
    let sales_rep = OBModules.OBAdPSASystemModule.get_data('sales_rep');

    document.getElementById('layout_main_container').innerHTML = '<div class="center-items"><h1>Your campaign is being submitted.</h1>    <div class="loader-inner ball-pulse"><div></div><div></div><div></div></div>\
    <p>Please do not close this tab. Thank you.</p></div>';

    OB.API.post('obadpsadsystemmodule', 'create_invoice', {
      'advertiser': advertiser, 'billing_mailing_address': address,
      'billing_mailing_city': city, 'billing_mailing_state_prov': state_prov, 'billing_mailing_zip_code': zip_code,
      'billing_email': bill_to_email, 'devices': '1',
      'creative': creative, 'ad_id': ad_id, 'time_slots': time_slots, 'media_type': media_type, 'media_file_id': 'TEST',
      'billing_mailing_country' : country, 'revenue_type': revenue_type,
      'start_date': start_date, 'stop_date': stop_date, 'campaign_name': campaign_name, 'buyer': buyer,
      'contract_number': contract_number, 'media_file_id': media_id, 'campaign_notes': campaign_notes, 'sales_rep': sales_rep
    }, function(res) {
      console.log(res);
      if (res.status) {
        console.log('test');
        document.getElementById('layout_main_container').innerHTML = '<div class="center-items"><h1>Your campaign has been submitted!</h1>\
          <p>Your message has been submitted.<br>Thank you for using the media/invoice wizard.\
          <br>You should now be able to view your media in the slidebar.</p></div';
      } else {
        document.getElementById('layout_main_container').innerHTML = 'Your invoice couldn\'t be submited. If the issue continues please contact the server admin.';
      }
    });
  }

  // gets all inputs, and selects on page,
  // then adds then the fields array.

  this.get_page_fields = function()
  {
    //this.fields = [];
    //let inputs = document.getElementsByTagName('input');
    let inputs = document.querySelectorAll('.layout_main input');
    //let selects = document.getElementsByTagName('select');
    let selects = document.querySelectorAll('.layout_main select');

    // loop though and add all inputs (text, email, etc) to array.
    for (var i = 0; i < inputs.length; i++) {
      this.fields.push(inputs[i]);
    }
    // loop though and add all selects (State, Province, etc) to array.
    for (var i = 0; i < selects.length; i++) {
      this.fields.push(selects[i]);
    }

  }

  this.fields_check = function(next_page)
  {
    if (OBModules.OBAdPSASystemModule.disable_checks) {
      return;
    }
    let data = [];
    for (var i = 0; i < OBModules.OBAdPSASystemModule.fields.length; i++) {
        let feild = OBModules.OBAdPSASystemModule.fields[i];
        //console.log('field', field);
        if (feild != null && feild.name != '') {
          data.push({'name': feild.name, 'value': feild.value});
          if (feild.reportValidity() == false) {
            //return {'status': false, 'msg': ''};
            break;
          }
      }
    }
    let json_data = JSON.stringify(data);
    console.log(json_data);
    if (next_page == 'review1.html') {
      OB.API.post('obadpsadsystemmodule', 'verify_invoice_data', {'data': json_data, 'no_ad_id': OBModules.OBAdPSASystemModule.get_data('has_ad_id')}, function(res) {
        if (res.status == false) {
          $('#ad_psa_system_status_message').obWidget('error', res.msg);
        } else {
          OBModules.OBAdPSASystemModule.get_page(next_page, true);
        }
      });
    } else {
      OB.API.post('obadpsadsystemmodule', 'verify_invoice_data', {'data': json_data}, function(res) {
        if (res.status == false) {
          $('#ad_psa_system_status_message').obWidget('error', res.msg);
        } else {
          OBModules.OBAdPSASystemModule.get_page(next_page, true);
        }
      });
    }
}

  this.review1_fill = function()
  {
    let review1_field_names = ['advertiser', 'address', 'city',
                              'zip_code', 'bill_to_email',
                              'start_date', 'stop_date', 'campaign_name', 'state_prov',
                              'advertiser_notes', 'time_slots', 'country', 'buyer',
                              'stations', 'contract_number', 'revenue_type'];

    review1_field_names.forEach(field => {
      if (field == 'start_date' || field == 'stop_date') {
        if (field.startsWith('start')) {
          let start_date = OBModules.OBAdPSASystemModule.get_data('start_date');
          let start_time_hour = OBModules.OBAdPSASystemModule.get_data('start_time_hour');
          document.getElementById('start_date_time').innerHTML = `${start_date} ${start_time_hour} local`;
        } else {
          let stop_date = OBModules.OBAdPSASystemModule.get_data('stop_date');
          let stop_time_hour = OBModules.OBAdPSASystemModule.get_data('stop_time_hour');
          document.getElementById('stop_date_time').innerHTML = `${stop_date} ${stop_time_hour} local`;
        }
      } else {
        let value = OBModules.OBAdPSASystemModule.get_data(field);
        let ele = document.getElementById(field);
        ele.innerHTML = value;
      }
    });
    OBModules.OBAdPSASystemModule.load_current_top_screen_values();
  }

  // Fills the second review page fields.

  this.review2_fill = function()
  {
    let review2_field_names = ['media_type', 'creative', 'ad_id'];

    review2_field_names.forEach(field => {
      if (field == 'media_type') {
        let value = OBModules.OBAdPSASystemModule.get_data(field);
        console.log('value', value);
        if (value == 1) {
          document.getElementById(field).innerHTML = 'Uploaded Media';
        } else {
          document.getElementById(field).innerHTML = 'TTS audio';
        }
      } else {
        let value = OBModules.OBAdPSASystemModule.get_data(field);
        let ele = document.getElementById(field);
        ele.innerHTML = value;
      }
    });
    OBModules.OBAdPSASystemModule.load_current_top_screen_values();
  }

  // fills page fields for later use.

  this.fill_page_fields = function(page)
  {
    switch (page) {
      case "review1.html":
        OBModules.OBAdPSASystemModule.review1_fill();
        break;
      case "review2.html":
        OBModules.OBAdPSASystemModule.review2_fill();
        break;
      default:
        //
    }
  }

  // Method to switch to, and load a page feilds when going back in the wizzard.

  this.go_back = function(page)
  {
    OBModules.OBAdPSASystemModule.get_page(page, true);
    switch (page) {
      case 'start.html':
        setTimeout(() => {
          document.getElementById('contract_number').value = OBModules.OBAdPSASystemModule.get_data('contract_number');
          document.getElementById('revenue_type').value = OBModules.OBAdPSASystemModule.get_data('revenue_type');
          document.getElementById('start_date').value = OBModules.OBAdPSASystemModule.get_data('start_date');
          document.getElementById('start_time_hour').value = OBModules.OBAdPSASystemModule.get_data('start_time_hour');
          document.getElementById('stop_date').value = OBModules.OBAdPSASystemModule.get_data('stop_date');
          document.getElementById('stop_time_hour').value = OBModules.OBAdPSASystemModule.get_data('stop_time_hour');
          document.getElementById('campaign_name').value = OBModules.OBAdPSASystemModule.get_data('campaign_name');
          document.getElementById('advertiser').value = OBModules.OBAdPSASystemModule.get_data('advertiser');
          document.getElementById('buyer').value = OBModules.OBAdPSASystemModule.get_data('buyer');
          OBModules.OBAdPSASystemModule.fill_dropdown('stations', OBModules.OBAdPSASystemModule.get_data('stations'));
          OBModules.OBAdPSASystemModule.fill_dropdown('time_slots', OBModules.OBAdPSASystemModule.get_data('time_slots'));
          document.getElementById('address').value = OBModules.OBAdPSASystemModule.get_data('address');
          document.getElementById('city').value = OBModules.OBAdPSASystemModule.get_data('city');
          document.getElementById('state_prov').value = OBModules.OBAdPSASystemModule.get_data('state_prov');
          document.getElementById('country').value = OBModules.OBAdPSASystemModule.get_data('country');
          document.getElementById('zip_code').value = OBModules.OBAdPSASystemModule.get_data('zip_code');
          document.getElementById('bill_to_email').value = OBModules.OBAdPSASystemModule.get_data('bill_to_email');
        }, 800);
        break;
        case 'media_info.html':
        setTimeout(() => {
          document.getElementById('creative').value = OBModules.OBAdPSASystemModule.get_data('creative');
          document.getElementById('ad_id').value = OBModules.OBAdPSASystemModule.get_data('ad_id');
          OBModules.OBAdPSASystemModule.fill_dropdown('audio_type', OBModules.OBAdPSASystemModule.get_data('audio_type'));
          OBModules.OBAdPSASystemModule.fill_dropdown('voice', OBModules.OBAdPSASystemModule.get_data('voice'));
          document.getElementById('message_text').value = OBModules.OBAdPSASystemModule.get_data('message_text');
          document.getElementById('tts_speed').value = OBModules.OBAdPSASystemModule.get_data('tts_speed');
          OBModules.OBAdPSASystemModule.fill_dropdown('bed_music', OBModules.OBAdPSASystemModule.get_data('bed_music'));
        }, 800);
        break;
    }
  }
  // Events listener for a default language.
  // This method handles enabling the subsets.
  this.language_selected = function()
  {
    let default_language = document.getElementById('default_language').value;
    OBModules.OBAdPSASystemModule.set_data('default_language', default_language);
    // Clear the language sets before adding back to the
    // dropdown for them.
    document.getElementById('language_sets').innerHTML = '';
    // We only need to handle languages with subsets here.
    switch (default_language) {
      case 'English':
        let options1 = ['Australian', 'British', 'Indian', 'US', 'Welsh'];
        options1.forEach((option) => {
          OBModules.OBAdPSASystemModule.dropdown_add_item('language_sets', option);
        });
        break;
      case 'Spanish':
        let options2 = ['European', 'Mexican', 'US Spanish'];
        options2.forEach((option) => {
          OBModules.OBAdPSASystemModule.dropdown_add_item('language_sets', option);
        });
        break;
      case 'French':
        let options3 = ['None', 'Canadian'];
        options3.forEach((option) => {
          OBModules.OBAdPSASystemModule.dropdown_add_item('language_sets', option);
        });
        break;
      case 'Portuguese':
        let options4 = ['Brazilian', 'European'];
        options4.forEach((option) => {
          OBModules.OBAdPSASystemModule.dropdown_add_item('language_sets', option);
        });
        break;
    }
  }

  // Event listner for the language sets.
  this.language_subset_selected = function()
  {
    let language_sets = $('#language_sets').val();
    let values = language_sets.join(",");
    OBModules.OBAdPSASystemModule.set_data('language_sets', language_sets);
  }

  // Method to switch pages in the Wizard.

  this.get_page = async function(page, bypass_feild_check = false)
   {
     if (bypass_feild_check == false) {
       OBModules.OBAdPSASystemModule.get_page_fields();
       OBModules.OBAdPSASystemModule.fields_check(page)
     } else {
       setTimeout(() => {
         OB.UI.replaceMain(`modules/obadpsasystem/${page}`);
         if (page == 'start.html') {
           setTimeout(OBModules.OBAdPSASystemModule.load_buyers, 500);
           OBModules.OBAdPSASystemModule.start_page_data_init();
         }
         if (page == 'payment.html') {
           start_payment_init();
         } else if (page == 'media_info.html') {
           OBModules.OBAdPSASystemModule.media_data_init();
         }

         if (page == 'review1.html') {
           OBModules.OBAdPSASystemModule.fill_page_fields(page);
         } else if (page == 'review2.html') {
           OBModules.OBAdPSASystemModule.fill_page_fields(page);
         }

         if (page == 'submiting.html') {
           console.log('Submiting invoice/AD message.');
           OBModules.OBAdPSASystemModule.submit_invoice();
         }

         OBModules.OBAdPSASystemModule.get_page_fields();
         this.next_btn = document.getElementById('next_btn');
         this.fields = document.getElementsByTagName('input');
         this.fields = document.getElementsByTagName('input');
         if (this.next_btn != null) {
           this.next_btn.addEventListener('click', (e) => {
             e.preventDefault();
             //OBModules.OBAdPSASystemModule.fields_check();
           });
         }
       }, 500)
     }
  }

  // Updates the enabled langauges.
  this.update_enabled_languages = function ()
  {
    let output = [];
    let english_checkbox = document.getElementById('English_checkbox').checked;
    //output.push({'english': english_checkbox});
    if (english_checkbox) output.push('english');
    let chinese_mandarin_checkbox = document.getElementById('Chinese_Mandarin_checkbox').checked;
    //output.push({'chinese_mandarin': chinese_mandarin_checkbox});
    if (chinese_mandarin_checkbox) output.push('chinese_mandarin');
    let dutch_checkbox = document.getElementById('Dutch_checkbox').checked;
    //output.push({'dutch': dutch_checkbox});
    if (dutch_checkbox) output.push('dutch');
    let french_checkbox = document.getElementById('French_checkbox').checked;
    //output.push({'french': french_checkbox});
    if (french_checkbox) output.push('french');
    let german_checkbox = document.getElementById('German_checkbox').checked;
    if (german_checkbox) output.push('german');
    //output.push({'german': german_checkbox});
    let italian_checkbox = document.getElementById('Italian_checkbox').checked;
    if (italian_checkbox) output.push('italian');
    //output.push({'italian': italian_checkbox});
    let portuguese_checkbox = document.getElementById('Portuguese_checkbox').checked;
    if (portuguese_checkbox) output.push('portuguese');
    //output.push({'portuguese': portuguese_checkbox});
    let spanish_checkbox = document.getElementById('Spanish_checkbox').checked;
    if (spanish_checkbox) output.push('spanish');
    //output.push({'spanish': spanish_checkbox});
    OBModules.OBAdPSASystemModule.set_data('enabled_languages', output.join(','));
  }

  // Handles dislabing the Ad-ID field if the user
  // checks the box.
  this.disable_ad_id = function ()
  {
    let ad_id_ele = document.getElementById('ad_id_row');
    let is_checked = document.getElementById('has_ad_id').checked;

    if (is_checked) {
      ad_id_ele.style.visibility = 'hidden';
    } else {
      ad_id_ele.style.visibility = 'visible';
    }
  }

  this.set_data = function (name, value)
  {
    if (value == '') {
      sessionStorage.removeItem(name);
      console.log(`Storing ${name} from the local system.`);
    } else {
      sessionStorage.setItem(name, value);
      console.log(`Storing ${name} with value ${value}`);
    }
    sessionStorage.setItem(name, value);
  }
  this.get_data = function (name)
  {
    let value = sessionStorage.getItem(name);
    console.log(`Getting ${value} from key ${name}`);
    return value;
  }

  // https://stackoverflow.com/questions/6234773/can-i-escape-html-special-chars-in-javascript

  this.escape_html = function (value) {
    return value
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
  }

  // Enables next buttons in the wizzard.

  this.enable_next_btn = function ()
  {
    let btns = document.getElementsByClassName('next_btn');
    btns.forEach(btn => {
      btn.disabled = false;
      console.log(btn);
    });
  }

  // Adds item to html select tag
  this.dropdown_add_item = function(select, item)
  {
    let select_ele = document.getElementById(select);
    let option_ele = document.createElement("option");
    option_ele.text = item;
    select_ele.add(option_ele);
  }

}
