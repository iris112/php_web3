<?php

require_once('vendor/autoload.php');

use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use EthereumPHP\EthereumClient;
use EthereumPHP\Types\BlockNumber;
use EthereumPHP\Types\Address;
use EthereumPHP\Types\Ether;
use EthereumPHP\Types\TransactionHash;
use Web3p\EthereumTx\Transaction;
use Ethereum\Ethereum;
use Ethereum\DataType\EthD32;

$PRIVATE_KEY = '0x.................';
$FromAccount = '0x.................';
$ToAccount = '0x.................';
$client = new EthereumClient('https://ropsten.infura.io');
$web3 = new Web3(new HttpProvider(new HttpRequestManager('https://ropsten.infura.io', 5)));
$eth = new Ethereum('https://ropsten.infura.io');

function sendEther($fromAddress, $privateKey, $toAddress, $amount) {
    global $web3, $client;
    $web3->eth->getTransactionCount($fromAddress, 'pending', function ($err, $nonce) 
                            use($fromAddress, $toAddress, $web3, $privateKey, $client, $amount) {
        if ($err !== null) {
            echo "Error: " . $err->getMessage();
        }
        echo "Nonce: ", $nonce, PHP_EOL;
        $transaction = new Transaction([
            'nonce' => '0x' . $web3->utils->toHex($nonce),
            'from' => $fromAddress,
            'to' => $toAddress,
            'gasLimit' => '0x' . $web3->utils->toHex(100000),
            'gasPrice' => '0x' . $web3->utils->toHex(10000000000),
            'value' => '0x' . $web3->utils->toHex($web3->utils->toWei($amount, 'ether')),
            'chainId' => 3,
            'data' => ''
        ]);

        $transaction->sign($privateKey);
        $serializedTx = $transaction->serialize();
        $web3->eth->sendRawTransaction(sprintf('0x%s', $serializedTx), function ($err, $tx) {
            if ($err !== null) {
                echo "Error: " . $err->getMessage(), PHP_EOL;
                return;
            }
            echo 'TX: ' . $tx, PHP_EOL;
        });
    });
}

function getBalance($address) {
    global $client;
    $myEthBalance = $client->eth()->getBalance(new Address($address), new BlockNumber())->toEther();
    echo "My Balance: ", $myEthBalance , PHP_EOL;
}

function getTransaction($Txhash) {
    global $client, $web3, $eth;
    $transaction = $client->eth()->getTransactionByHash(new TransactionHash($Txhash));
    $transactionReceipt = $eth->eth_getTransactionReceipt(new EthD32($Txhash));
    $latestBlockNumber = $client->eth()->blockNumber();
    echo "Block Hash:       ", $transaction->blockHash(), PHP_EOL;
    echo "Block Number:     ", $transaction->blockNumber(), PHP_EOL;
    echo "From:             ", $transaction->from(), PHP_EOL;
    echo "To:               ", $transaction->to(), PHP_EOL;
    echo "Hash:             ", $transaction->hash(), PHP_EOL;
    echo "Nonce:            ", $transaction->nonce(), PHP_EOL;
    echo "Amount:           ", $transaction->value()->toEther(), PHP_EOL;
    echo "Fee:              ", sprintf('%f', $transactionReceipt->gasUsed->val() * 0.00000001), PHP_EOL;
    echo "Confirmations:    ", $latestBlockNumber - $transaction->blockNumber() + 1, PHP_EOL;
}

function checkTransactionHash($Txhash) {
    global $client;
    try {
        $transaction = $client->eth()->getTransactionByHash(new TransactionHash($Txhash));
        $latestBlockNumber = $client->eth()->blockNumber();

        return $latestBlockNumber - $transaction->blockNumber() + 1;
    } catch (Exception $e) {
        return 0;
    } catch (Error $e) {
        return 0;
    }
}

// getBalance($FromAccount);
// sendEther($FromAccount, $PRIVATE_KEY, $ToAccount, '0.002');
// getTransaction('0x.................');
// echo checkTransactionHash('0x.................');


