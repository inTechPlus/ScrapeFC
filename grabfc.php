<?php
class fetchFC {
	function __construct($username, $password, $message) {
		$this->username = "$username"; 
		$this->password = "$password";
		$this->redirct = "index.php";
		$this->Login = "Login";
		$this->url = "http://forumcoin.com/ucp.php?mode=login";
		$this->cookie = getcwd()."/".uniqid("cookie_").".txt";
		
		$this->title = NULL;
		$this->sender = NULL;
		$this->receiver = NULL;
		$this->amount = NULL;
		$this->comment = NULL;

		$this->postdata = "username=".$this->username."&password=".$this->password."&redirect=".$this->redirct."&login=".$this->Login; 

		$this->ch = curl_init(); 
		curl_setopt ($this->ch, CURLOPT_URL, $this->url); 
		curl_setopt ($this->ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6"); 
		curl_setopt ($this->ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($this->ch, CURLOPT_COOKIEJAR, $this->cookie); 
		curl_setopt ($this->ch, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt ($this->ch, CURLOPT_POSTFIELDS, $this->postdata); 
		curl_setopt ($this->ch, CURLOPT_POST, 1); 
		$this->data = curl_exec($this->ch);
		
		// Make second request
		$this->url = "http://forumcoin.com//ucp.php?i=pm&mode=view&f=0&p=$message";
		curl_setopt ($this->ch, CURLOPT_URL, $this->url); 
		curl_setopt ($this->ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6"); 
		curl_setopt ($this->ch, CURLOPT_RETURNTRANSFER, 1); 
		$this->data = curl_exec($this->ch);
		
		require_once "simple_html_dom.php";
		
		$this->html = str_get_html("$this->data");
		
		// Fetch title
		foreach($this->html->find('.first') as $this->title) {
			$this->title = $this->title->plaintext;
		}
		
		if ($this->title == "You have received a donation!") {	
			// Find ForumCoin amount
			foreach($this->html->find('.content') as $this->amount) {
				$this->string = explode(":", $this->amount->plaintext);
				$this->amount = filter_var($this->string[0], FILTER_SANITIZE_NUMBER_INT); // Needed to include one line only.
			}
			
			// Find sender
			foreach($this->html->find('.author') as $this->author) {
				$this->sender = explode("To:", $this->author->plaintext);
				$this->sender = explode(" :morF", strrev($this->sender[0]));
				$this->sender = strrev($this->sender[0]);
			}

			// Find receiver
			foreach($this->html->find('.author') as $this->author) {
				$this->receiver = explode(" :oT", strrev($this->author->plaintext));
				$this->receiver = strrev($this->receiver[0]);
			}
			
			// Find comment made
			foreach($this->html->find('.content i') as $this->comment) {
				$this->comment = $this->comment->plaintext;
			}
		}
		curl_close($this->ch);
		fopen($this->cookie, "w");
		unlink($this->cookie);
	}
}

class sendFC {
	function __construct($username, $password, $uid, $amount, $comment = "") {
		$this->username = "$username";
		$this->password = "$password";
		$this->user = "$uid";
		$this->amount = "$amount";
		$this->comment = "$comment";
		$this->redirect = "index.php";
		$this->Login = "Login";
		$this->url = "http://forumcoin.com/ucp.php?mode=login"; 
		$this->cookie = getcwd()."/".uniqid("cookie_").".txt";

		$this->postdata = "username=".$this->username."&password=".$this->password."&redirect=".$this->redirect."&login=".$this->Login;
		
		$this->ch = curl_init(); 
		curl_setopt ($this->ch, CURLOPT_URL, $this->url); 
		curl_setopt ($this->ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6"); 
		curl_setopt ($this->ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($this->ch, CURLOPT_COOKIEJAR, $this->cookie); 
		curl_setopt ($this->ch, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt ($this->ch, CURLOPT_POSTFIELDS, $this->postdata); 
		curl_setopt ($this->ch, CURLOPT_POST, 1); 
		$this->result = curl_exec($this->ch); 	
		
		// make second request
		$this->url = "http://forumcoin.com/points.php?mode=transfer&i=$this->user";
		curl_setopt ($this->ch, CURLOPT_URL, $this->url); 
		curl_setopt ($this->ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6"); 
		curl_setopt ($this->ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($this->ch, CURLOPT_COOKIEJAR, $this->cookie); 
		curl_setopt ($this->ch, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt ($this->ch, CURLOPT_POST, 1); 
		$this->data = curl_exec($this->ch);
		
		require_once "simple_html_dom.php";
		
		// Create DOM from URL or file
		$this->html = str_get_html("$this->data");
		
		// Fetch form creation time
		foreach($this->html->find('input[name=creation_time]') as $this->creation) {
			// echo htmlspecialchars($this->creation);
			$this->creation = strrev(substr(strrev(substr(htmlspecialchars($this->creation), 77)), 11));
		}
		
		// echo "<br>$this->creation<br>";
		
		// Fetch token
		foreach($this->html->find('input[type=hidden]') as $this->token) {
			// echo htmlspecialchars($this->token);
			$this->token = strrev(substr(strrev(substr(htmlspecialchars($this->token), 74)), 11));
		}
		
		// echo "<br>$this->token<br>";
		
		sleep(2);
		// make third request
		$this->url = "http://forumcoin.com/points.php?mode=transfer&i=$this->user";
		$this->submit = "Submit";
		$this->time = "$this->creation";
		$this->formToken = "$this->token";
		$this->postdata = "amount=".$this->amount."&submit=".$this->submit."&creation_time=".$this->time."&form_token=".$this->formToken."&comment=".$this->comment;
		curl_setopt ($this->ch, CURLOPT_URL, $this->url); 
		curl_setopt ($this->ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6"); 
		curl_setopt ($this->ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($this->ch, CURLOPT_COOKIEJAR, $this->cookie); 
		curl_setopt ($this->ch, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt ($this->ch, CURLOPT_POSTFIELDS, $this->postdata); 
		curl_setopt ($this->ch, CURLOPT_POST, 1); 

		$this->data = curl_exec($this->ch);
		
		$this->html = str_get_html("$this->data");
		
		// Fetch form creation time
		foreach($this->html->find('.inner') as $this->inner) {
			// echo htmlspecialchars($this->html);
			$this->inner = substr(htmlspecialchars($this->inner), 160);
		}
		
		if (substr($this->inner, 0, 28) == "You successfully transferred") {
			$this->success = "Success!";
		}
		else {
			$this->success = "Failed ...";
		}
		
		curl_close($this->ch);
		fopen($this->cookie, "w");
		unlink($this->cookie);
	}
}
?>