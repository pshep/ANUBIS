<?
error_reporting('E_ALL');
ini_set('display_errors','On'); 


$group_totals = array('GRP_id' => 0, 'BTC_received' => 0, 'BTC_sent' => 0, 'BTC_balance' => 0);

$mtgox_currencys = array ('USD', 'GBP', 'EUR', 'AUD', 'CAD', 'CHF', 'CNY', 'DKK',
                          'HKD', 'JPY', 'NZD', 'PLN', 'RUB', 'SEK', 'SGD', 'THB');

$mtgox_url = 'https://mtgox.com/api/1/';
$mtgox_exchange_path = '/public/ticker';

$blockchain_url = 'http://www.blockchain.info/';
$blockchain_addr_options = '?format=json&limit=0';
$blockchain_addr_path = 'address/';

$exchange_rate = 0;
$currency_code = 'USD';

$opts = array(
  'http' => array(
    'method'=>"GET",
    'user_agent'=> 'hashcash',
    'header'=>"Accept-language: en\r\n",
    'timeout' => 3
  )
);

function create_accounts_table()
{
  global $dbh;
  global $primary_key, $table_props;

    $tblstr = "
  CREATE TABLE IF NOT EXISTS `accounts` (
    `id` ".$primary_key.",
    `group` mediumint(6) DEFAULT '0',
    `name` varchar(255) NOT NULL,
    `address` varchar(34) NOT NULL
  )".$table_props.";";

  $dbh->query($tblstr);
}

function create_accgroups_table()
{
  global $dbh;
  global $primary_key, $table_props;

    $tblstr = "
  CREATE TABLE IF NOT EXISTS `accgroups` (
    `id` ".$primary_key. ",
    `name` varchar(255) NOT NULL,
    `currency` varchar(3) NOT NULL DEFAULT 'USD'
  )".$table_props.";";

  $dbh->query($tblstr);
}

function create_group_header($group_data)
{
  global $group_totals;
  global $mtgox_url;
  global $mtgox_exchange_path;
  global $exchange_rate;
  global $currency_code;
  global $opts;

  /* reset BTC counters */
  $group_totals['GRP_id'] = $group_data['id'];
  $group_totals['BTC_received'] = 0;
  $group_totals['BTC_sent'] = 0;
  $group_totals['BTC_balance'] = 0;

  /* get exchange rate data from mtgox */
  $currency_code = $group_data['currency'];
  $url = $mtgox_url . "BTC" . $currency_code . $mtgox_exchange_path;
 

  $context  = stream_context_create($opts);
    
  $url_data = file_get_contents($url,false,$context);

  $mtgox_arr = json_decode($url_data, true);

  $exchange_rate = $mtgox_arr['return']['last_local']['value'];

  $line =
  "<tr>
    <th colspan='7'>".
      $group_data['name']
    ."</th>
  </tr>
  <tr>
    <th>
      &nbsp;
    </th>
    <th>
      Account Name
    </th>
    <th>
      Account Address
    </th>
    <th>
      Received
    </th>
    <th>
      Sent
    </th>
    <th>
      Balance
    </th>
    <th>
      ".$group_data['currency']." (".round($exchange_rate,2).")
    </th>
  </tr>";
  
  return $line;
}


function get_acc_summary($acc_data)
{
  global $group_totals;
  global $blockchain_url;
  global $blockchain_addr_path;
  global $blockchain_addr_options;

  global $exchange_rate;
  global $opts;

  /* get data of address from blockchain.info */
  $btc_address = $acc_data['address'];
  $url = $blockchain_url . $blockchain_addr_path . $btc_address . $blockchain_addr_options;

  $context  = stream_context_create($opts);

  $url_data = file_get_contents($url,false,$context);

  $acc_arr = json_decode($url_data, true);
  
  $btc_received = $acc_arr['total_received'];
  $btc_sent = $acc_arr['total_sent'];
  $btc_balance = $acc_arr['final_balance'];

  $group_totals['BTC_received'] += $btc_received;
  $group_totals['BTC_sent'] += $btc_sent;
  $group_totals['BTC_balance'] += $btc_balance;

  $btc_received /= 100000000;
  $btc_sent /= 100000000;
  $btc_balance /= 100000000;

  $exchanged_balance = $btc_balance * $exchange_rate;

  $line =
  "<tr>
    <td>
      <input type='checkbox' name='del_acc[]' value='".$acc_data['id']."'>
    </td>
    <td>".
      $acc_data['name']
    ."</td>
    <td><a href='".$blockchain_url.$blockchain_addr_path.$btc_address."'>".
      $btc_address
    ."</a></td>
    <td>".
      $btc_received
    ."</td>
    <td>".
      $btc_sent
    ."</td>
    <td>".
      $btc_balance
    ."</td>
    <td>".
      round($exchanged_balance, 2)
    ."</td>
  </tr>";

  return $line;
}

function create_group_totals()
{
  global $group_totals;
  global $exchange_rate;

  $btc_received = $group_totals['BTC_received'] / 100000000;
  $btc_sent = $group_totals['BTC_sent'] / 100000000;
  $btc_balance = $group_totals['BTC_balance'] / 100000000;
  $exchanged_balance = $btc_balance * $exchange_rate;

  $line =
  "<tr>
    <th>
      <input type='checkbox' name='deletegrp' value='".$group_totals['GRP_id']."'>
    </th>
    <th colspan='2'><div style='text-align:right'>
     Totals:</div>
    </th>
    <th>".
      $btc_received
    ."</th>
    <th>".
      $btc_sent
    ."</th>
    <th>".
      $btc_balance
    ."</th>
    <th>".
      round($exchanged_balance, 2)
    ."</th>
  </tr>
  <tr>
    <th colspan='7'>
      Name: <input type='text' name='name'>&nbsp;
      Address: <input type='text' name='address'>&nbsp;
      <input type='submit' value='Add Account' name='addacc'>
      <input type='hidden' name='groupid' value='".$group_totals['GRP_id']."'>
      &nbsp; &nbsp;
      <input type='submit' value='Delete selected' name='delete'>
    </th>
  </tr>
  ";

  return $line;
}


?>
