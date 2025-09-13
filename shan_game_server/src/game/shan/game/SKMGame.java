package game.shan.game;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.util.ArrayList;
import java.util.Collections;
import java.util.Comparator;
import java.util.Iterator;
import java.util.List;
import java.util.Random;
import java.util.concurrent.CompletableFuture;

import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.CloseableHttpResponse;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.ContentType;
import org.apache.http.entity.StringEntity;
import org.apache.http.impl.client.HttpClients;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.smartfoxserver.v2.entities.Room;
import com.smartfoxserver.v2.entities.User;
import game.shan.ShanExtension;
import game.shan.card.Card;
import game.shan.card.Deck;
import game.shan.constant.Constants;
import game.shan.handlers.TransactionPlayer;
import game.shan.serializeclasses.BalanceData;
import game.shan.serializeclasses.GetBalanceReq;
import game.shan.serializeclasses.GetBalanceResponse;
import game.shan.serializeclasses.GetBalanceUser;
import game.shan.serializeclasses.ResultBanker;
import game.shan.serializeclasses.ResultPlayer;
import game.shan.serializeclasses.TransactionData;
import game.shan.serializeclasses.TransactionResponse;
import game.shan.serializeclasses.TransitionReq;
import game.shan.utils.RoomHelper;
import game.shan.utils.TaskHelper;

public class SKMGame {
	private ShanExtension _shanExtension;
	
	private ArrayList<RoomPlayer> _players;
	private ArrayList<RoomPlayer> _doPlayers;
	private ArrayList<RoomPlayer> _playersToLeave;
	public ArrayList<User> _usersWaiting;
	
	private ArrayList<RoomPlayer> _winPlayers;
	private ArrayList<RoomPlayer> _losePlayers;
	
	private ArrayList<RoomPlayer> _bots;
	//private SKMPlayer getBanker();
	private int _playerDoneIndex = 0;
	private int _playerBet = 0;
	
	private Deck _cardDeck;
	
	private RoomPlayer _currentPlayer;
	private int _turnIndex;
	private int _curBankerIndex = 0;
	private int _curBankerTurnCount = 0; //5 max
	private boolean _changingBank = true;
	private int _curBankAmount = 0;
	private int _roomBankAmount = 0;
	
	private boolean _sentOwner = false;
	private boolean _isIdle = false;
	public boolean processingWinLose = false;
	public boolean isWarning = false;
	
	HttpClient client;
	
	public int getBankAmount() { return _curBankAmount;}
	
	public SKMGame(ShanExtension ext) {
		
		client = HttpClients.createDefault();
		_shanExtension = ext;
		_players = new ArrayList<RoomPlayer>();
		_playersToLeave = new ArrayList<RoomPlayer>();
		_usersWaiting = new ArrayList<User>();
		_winPlayers = new ArrayList<RoomPlayer>();
		_losePlayers = new ArrayList<RoomPlayer>();
		_doPlayers = new ArrayList<RoomPlayer>();
	    _bots = new ArrayList<RoomPlayer>();
		_cardDeck = new Deck();
		_curBankerIndex = 1;
		_isIdle = true;
		isWarning = false;
		//_curBankAmount = 5000;
		/*
		 * try { SendTestLoginToWebServer(); SendTransitionToWebServer();
		 * 
		 * } catch (Exception e) { // TODO Auto-generated catch block
		 * e.printStackTrace(); }
		 */
	}
	
	public RoomPlayer getPlayerByUser(User user) {
	    return _players.stream()
	            .filter(p -> p.playerName.equals(user.getName()))
	            .findFirst()
	            .orElse(null);
	}
	
	private RoomPlayer getPlayerByName(String name) {
	    return _players.stream()
	            .filter(p -> p.playerName.equals(name))
	            .findFirst()
	            .orElse(null);
	}
	
	public void processPlayerLeave(User user) {
		String name = user.getName();
		
		if(_usersWaiting.contains(user)) {
			_usersWaiting.remove(user);
			return;
		}
		
		RoomPlayer leavePlayer = getPlayerByName(name);
		
		if(leavePlayer == null)
		{
			return;
		}
		
		/*
		 * if (_currentPlayer == leavePlayer) { if(getBanker() == _currentPlayer) {
		 * processWinLoseFinal(); } else { processTurnChange(); } }
		 */
		
		if(_isIdle && RoomHelper.getCurrentRoom(_shanExtension).getUserList().size() == 0) {
			_players = new ArrayList<RoomPlayer>();
			_bots = new ArrayList<RoomPlayer>();
			_playersToLeave = new ArrayList<RoomPlayer>();
			return;
		}
		
		_playersToLeave.add(leavePlayer);
		leavePlayer.isPlayerLeft = true;
		
	}

	public RoomPlayer getBanker() {
	    return _players.stream()
	            .filter(p -> p.isBanker())
	            .findFirst()
	            .orElse(null);
	}

	public void startGame() {
		resetGame();
		assignPlayers();
		_cardDeck.shuffleDeck();
		_shanExtension.scheduleStartGame();
		//sendBanker();

		
		_isIdle = false;
	}
	
	public void onGameStarts() {
		_shanExtension.trace("game started now");
		TaskHelper.startScheduleTask(_shanExtension, () -> distributeInitialCards(), 5);
	}
	
	public void onBetStarted() {
		for (RoomPlayer roomPlayer : _players) {
			if(roomPlayer.isBanker()) {
				continue;
			}
			
			_shanExtension.scheduleAutoBet(roomPlayer);
		}
	}
	
	public void sendStartDelay() {
		_shanExtension.scheduleBeforeStartGame();
	}
	
	public void sendOwnerWithDelay(User owner, boolean sendToOwner) {
		if(_sentOwner)
		{
			return;
		}
		
		_sentOwner = true;
		_shanExtension.scheduleSendOwner(owner, sendToOwner);
	}
	
	/*
	 * public void sendOwner(User owner, boolean sendToOwner) {
	 * _shanExtension.sendOwner(owner, sendToOwner); }
	 */
	
	public void sendBanker() {
		if(getBanker() == null)
		{
			return;
		}
		
		_shanExtension.sendBanker(getBanker());
	}
	
	public void assignPlayers() {
	    for (User user : _usersWaiting) {
	        _players.add(new RoomPlayer(user, false));
	    }

	    _usersWaiting.clear();

	    int minBet = RoomHelper.getCurrentRoom(_shanExtension).getVariable("minBet").getIntValue();

	    // Asynchronous API call
	    getBalancesFromWebServerAsync().thenAccept(response -> {
	        if (response == null) {
	            _shanExtension.trace("Failed to fetch balances.");
	            return;
	        }

	        for (BalanceData data : response.data) {
	            RoomPlayer player = getPlayerByName(data.member_account);
	            if (player != null) {
	            	_shanExtension.trace("finish fectching player balance here");
	                player.setTotalAmount(data.balance); // Or set data.balance if you want real balance
	            }
	        }

	        // Kicking players AFTER the balances are updated
	        kickPlayersBelowMinBet(minBet);
	        sendRoomPlayerList();
	    }).exceptionally(ex -> {
	        ex.printStackTrace();
	        return null;
	    });
	}
	
	private void kickPlayersBelowMinBet(int minBet) {
	    for (Iterator<RoomPlayer> iterator = _players.iterator(); iterator.hasNext();) {
	        RoomPlayer player = iterator.next();
	        _shanExtension.trace(player.playerName + " total amount : " + player.getTotalAmount());
	        
	        if ((player.getTotalAmount() < minBet) && !player.isBanker()) {
	            if (!player.isBotA && !player.isBotB) {
	                _shanExtension.getApi().leaveRoom(player.getSFSUser(), RoomHelper.getCurrentRoom(_shanExtension));
	                _shanExtension.trace("kicking player");
	            } else {
	                _bots.remove(player);

	                if (_players.size() <= 3 && _bots.size() == 0) {
	                    addRandomBots(RoomHelper.getCurrentRoom(_shanExtension), 1);
	                } else if (_bots.size() == 1) {
	                    addRandomBots(RoomHelper.getCurrentRoom(_shanExtension), 1);
	                } else if (_bots.size() == 0) {
	                    addRandomBots(RoomHelper.getCurrentRoom(_shanExtension), 2);
	                }
	            }

	            iterator.remove();
	        }
	    }
	    
	    setBanker(_players.get(_curBankerIndex));
		_curBankerTurnCount++;
		if(_curBankerTurnCount > 5) {
			_changingBank = true;
		}
		else if(_curBankerTurnCount >= 4) {
			isWarning = true;
		}
		_curBankerIndex = _curBankerIndex % _players.size();
		
		_turnIndex = _players.indexOf(getBanker());
	}

	public void processCreateBots(Room room) {
		_shanExtension.scheduleCreateBots(room);
		//_shanExtension.createBots(room);
	}
	
	public static String getRandomName() {
        java.util.Random random = new java.util.Random();
        int index = random.nextInt(Constants.BOT_NAMES.length); // Random index in the array
        return Constants.BOT_NAMES[index];
    }
	
	public void addBots(Room room) {
		if (_players.size() <= 3) {
		    // Create and add BotB
		    RoomPlayer botB = new RoomPlayer(getRandomName(), false, false);
		    _bots.add(botB);
		    //_shanExtension.sendBotJoined(botB);

		} else {
		    // Create and add BotA
		    RoomPlayer botA = new RoomPlayer(getRandomName(), true, false);
		    _bots.add(botA);
		    //_shanExtension.sendBotJoined(botA);

		    // Create and add BotB
		    RoomPlayer botB = new RoomPlayer(getRandomName(), false, false);
		    _bots.add(botB);
		    //_shanExtension.sendBotJoined(botB);
		}
		
		int minAmount = RoomHelper.getCurrentRoom(_shanExtension).getVariable("minAmount").getIntValue();
	    int maxAmount = RoomHelper.getCurrentRoom(_shanExtension).getVariable("maxAmount").getIntValue();
		
		for (RoomPlayer player : _bots) { 
			player.resetPlayerHand();
			_players.add(player); 
			Random random = new Random();
        	int randomAmount = minAmount + random.nextInt(maxAmount - minAmount + 1);
            player.setTotalAmount(randomAmount);
		  }
		
		//sendRoomPlayerList();

	}
	
	public void addRandomBots(Room room, int botCount) {
	    Random random = new Random();
	    int minAmount = RoomHelper.getCurrentRoom(_shanExtension).getVariable("minAmount").getIntValue();
	    int maxAmount = RoomHelper.getCurrentRoom(_shanExtension).getVariable("maxAmount").getIntValue();

	    for (int i = 0; i < botCount; i++) {
	        // Randomly decide bot type
	        boolean isBotA = random.nextBoolean();
	        
	        // Create the bot with random type
	        RoomPlayer bot = new RoomPlayer(getRandomName(), isBotA, false);
	        
	        // Add bot to _bots list
	        _bots.add(bot);
	        _players.add(bot);
	        bot.resetPlayerHand();
	        Random rand = new Random();
        	int randomAmount = minAmount + rand.nextInt(maxAmount - minAmount + 1);
        	bot.setTotalAmount(randomAmount);
	        //_shanExtension.sendBotJoined(bot);  // Uncomment to notify room when a bot joins
	    }
	    
	    // Add all bots to the _players list and reset their hands
		/*
		 * for (RoomPlayer bot : _bots) { bot.resetPlayerHand(); _players.add(bot); }
		 */
	    
	    //sendRoomPlayerList();
	}

	
	public void processAddPlayer(User user) {
		boolean isAlrdyInRoom  = false;
		
		for (RoomPlayer roomPlayer : _players) {
			if(roomPlayer.playerName == user.getName()) {
				isAlrdyInRoom = true;
				break;
			}
		}
		
		_shanExtension.trace("is joining user already in room : " + isAlrdyInRoom);
		if(_isIdle) {
			if(isAlrdyInRoom){
				_playersToLeave.remove(getPlayerByName(user.getName()));
				
				getPlayerByName(user.getName()).isPlayerLeft = false;
				getPlayerByName(user.getName()).setSFSUser(user);
				//sendRoomPlayerList();
				return;
			}
			
			_players.add(new RoomPlayer(user, false));
			//sendRoomPlayerList();
		}
		else {
			_usersWaiting.add(user);
			_shanExtension.pleaseWait(user);
		}
		
	}
	
	public void sendMatchEnd() {
		if(_curBankAmount > _roomBankAmount && _changingBank) {
			_shanExtension.sendMatchEnd(_curBankAmount - _roomBankAmount, true);
		}
		else {
			_shanExtension.sendMatchEnd(0, false);
		}
		
	}
	
	public void sendRoomPlayerList() {
		_shanExtension.sendRoomPlayerList(_players);
		sendBanker();
	}
	
	public void setBanker(RoomPlayer player) {
		if (!_changingBank || (getBanker() != null && getBanker() == player)) {
			return;
		}
		
		_curBankerIndex++;
		
		_roomBankAmount = RoomHelper.getCurrentRoom(_shanExtension).getVariable("bankerAmount").getIntValue();
		
		RoomPlayer curBanker = getBanker();
		
		if(curBanker != null)
		{
			curBanker.addToTotalAmount(_curBankAmount);
			
			 if(curBanker.playerID != -1) { 
//				 updateBalanceInDatabase(curBanker.playerID,
//				 curBanker.getTotalAmount()  ); 
				 }
			 
			_curBankAmount = 0;
			curBanker.setBankerOrNot(false);
			
		}
		player.setBankerOrNot(true);
		player.setTotalAmount(getBanker().getTotalAmount() - _roomBankAmount);
		
		 if(player.playerID != -1) { 
//			 updateBalanceInDatabase(player.playerID,
//			 player.getTotalAmount()  ); 
			 }
		 
		_curBankAmount = _roomBankAmount;

		_curBankerTurnCount = 1;
		_changingBank = false;
		isWarning = false;
	}
	
	public void processClientEmoji(RoomPlayer player, int index) {
		_shanExtension.sendClientEmojiToRoom(player, index);
	}
	
	public void processClientMessage(RoomPlayer player, String message) {
		_shanExtension.sendClientMessageToRoom(player, message);
	}
	
	public void processClientBet(RoomPlayer player) {
		int betAmount = RoomHelper.getCurrentRoom(_shanExtension).getVariable(Constants.MIN_BET).getIntValue();
		
		if(betAmount >= player.getTotalAmount()) {
			betAmount = player.getTotalAmount();
		}
		player.bet(betAmount);
		_shanExtension.sendBetInfo(player, betAmount);
		_playerBet ++;
		
		if(_playerBet >= _players.size() - 1) {
			//distributeInitialCards();
			_shanExtension.startGame();
		}
	}
	
	public void processClientBet(RoomPlayer player, int betAmount) {
		if(betAmount >= player.getTotalAmount()) {
			betAmount = player.getTotalAmount();
		}
		if(betAmount <= 0) {
			betAmount = RoomHelper.getCurrentRoom(_shanExtension).getVariable(Constants.MIN_BET).getIntValue();
		}
		player.bet(betAmount);
		_shanExtension.sendBetInfo(player, betAmount);
		_playerBet ++;
		
		if(_playerBet >= _players.size() - 1) {
			//distributeInitialCards();
			_shanExtension.trace("all players betted and start now");
			_shanExtension.startGame();
		}
	}
	
	public void distributeInitialCards() {
		_shanExtension.trace("distributed initial cards");
		for (RoomPlayer skmPlayer : _players) {
			skmPlayer.addCard(drawCardFromDeck());
			skmPlayer.addCard(drawCardFromDeck());
			
			_shanExtension.sendClientHandCards(skmPlayer);
		}
		
		checkDoAfterDistribute();
	}
	
	public void processClientDraw(User user) {

		_shanExtension.cancelDecisionTimer();
		
		if(processingWinLose) {
			return;
		}
		
		RoomPlayer player = getPlayerByUser(user);
		Card drawnCard = drawCardFromDeck();

		player.drawCard(drawnCard);
		
		_shanExtension.clientDrawCard(player, drawnCard);
		
		if(player.isBanker()) {
			// 3card or smth
			_shanExtension.scheduldFinalWinLose();
		}
		else {
			_playerDoneIndex++;
			processTurnChange();
			/*
			 * if(_playerDoneIndex >= _players.size()) { bankerTurn(); }
			 */
		}
	}
	
	public void processClientStand(User user) {
		_shanExtension.cancelDecisionTimer();
		
		if(processingWinLose) {
			return;
		}
		
		if(getPlayerByUser(user).playerName == getBanker().playerName) {
			_shanExtension.scheduldFinalWinLose();
		}
		else {
			_playerDoneIndex++;
			processTurnChange();
			/*
			 * if(_playerDoneIndex >= _players.size()) { bankerTurn(); }
			 */
		}
	}
	
	private void NormalDrawBot(RoomPlayer player)
	{
		_shanExtension.cancelDecisionTimer();
		if(processingWinLose) {
			return;
		}
		
		Card drawnCard = drawCardFromDeck();
		player.drawCard(drawnCard);
		_shanExtension.clientDrawCard(player, drawnCard);
		
		if(player.isBanker()) {
			_shanExtension.scheduldFinalWinLose();
		}
		else {
			processTurnChange();
		}
	}
	
	private void AdvancedDrawBot(RoomPlayer player) { //auto add to 6 7 8 9
		_shanExtension.cancelDecisionTimer();
		if(processingWinLose) {
			return;
		}
		
		for (int i = 0; i < _cardDeck.getCardDeck().size(); i++) {
			int total = player.getHandValue() + _cardDeck.viewCardByIndex(i).value;
			
			if ( total >= 6 && total <= 9) {
				player.drawCard(_cardDeck.drawDesireCardByIndex(i));
				_shanExtension.clientDrawCard(player, _cardDeck.drawDesireCardByIndex(i));
				if(player.isBanker()) {
					_shanExtension.scheduldFinalWinLose();
				}
				else {
					processTurnChange();
				}
				break;
			}
		}
	}

	public void processBotDecision(RoomPlayer player) {
		if(player.isBotB){
			if(player.isBanker()) {
				if(player.getHandValue() <= 3) {
					//processClientDraw(player);
					NormalDrawBot(player);
					
					
				}
				else {
					processClientStand(player);
				}
			}
			else {
				if(player.getHandValue() < 6) {
					AdvancedDrawBot(player);
				}
				else {
					processClientStand(player);
				}
			}
		}
		else if(player.isBotA) {
			if(!player.isBanker()) {
				if(player.getHandValue() <= 3) {
					//processClientDraw(player);
					NormalDrawBot(player);
				}
				else {
					processClientStand(player);
				}
			}
			else {
				if(player.getHandValue() < 6) {
					//processClientDraw(player);
					AdvancedDrawBot(player);
				}
				else {
					processClientStand(player);
				}
			}
		}
	}
	
	
	/*
	 * public void processClientDraw(SKMPlayer player) { //bot only
	 * if(player.isBotB){ if(!player.isBanker()) { for (int i = 0; i <
	 * _cardDeck.getCardDeck().size(); i++) { int total = player.getHandValue() +
	 * _cardDeck.viewCardByIndex(i).value;
	 * 
	 * if ( total >= 6 && total <= 9) {
	 * player.drawCard(_cardDeck.drawDesireCardByIndex(i));
	 * _shanExtension.clientDrawCard(player, _cardDeck.drawDesireCardByIndex(i));
	 * processTurnChange(); return; } } } else { Card drawnCard =
	 * drawCardFromDeck(); player.drawCard(drawnCard);
	 * _shanExtension.clientDrawCard(player, drawnCard);
	 * 
	 * _shanExtension.scheduldFinalWinLose(); } } else if(player.isBotA) {
	 * if(player.isBanker()) { for (int i = 0; i < _cardDeck.getCardDeck().size();
	 * i++) { int total = player.getHandValue() +
	 * _cardDeck.viewCardByIndex(i).value;
	 * 
	 * if ( total >= 6 && total <= 9) {
	 * player.drawCard(_cardDeck.drawDesireCardByIndex(i));
	 * _shanExtension.clientDrawCard(player, _cardDeck.drawDesireCardByIndex(i));
	 * processTurnChange(); _shanExtension.scheduldFinalWinLose(); return; } } }
	 * else { Card drawnCard = drawCardFromDeck(); player.drawCard(drawnCard);
	 * _shanExtension.clientDrawCard(player, drawnCard);
	 * 
	 * 
	 * } }
	 * 
	 * _shanExtension.trace(player.playerName + " has drawn a card"); }
	 */
	
	public void processClientStand(RoomPlayer player) { //bot only
		_shanExtension.cancelDecisionTimer();
		
		if(processingWinLose) {
			return;
		}
		
		if(player == getBanker()) {
			_shanExtension.scheduldFinalWinLose();
		}
		else {
			_playerDoneIndex++;
			processTurnChange();
			/*
			 * if(_playerDoneIndex >= _players.size()) { bankerTurn(); }
			 */
		}
	}

	
	
	
	private void checkDoAfterDistribute() {
		if(getBanker().isDo()) {
			_shanExtension.clientDo(getBanker());
			onBankDo();
		}
		else {
			for (RoomPlayer skmPlayer : _players) {
				if(skmPlayer.isDo()) {
					_playerDoneIndex++;
					//_shanExtension.clientDo(skmPlayer);
					_doPlayers.add(skmPlayer);
				}
			}
			
			if(_doPlayers.size() > 0 && _doPlayers.size() < _players.size()) {
				for (RoomPlayer doPlayer : _doPlayers) {
					_shanExtension.clientDo(doPlayer);
				}
				
				processTurnChange();
			}
			else if(_doPlayers.size() == _players.size()) {
				for (RoomPlayer doPlayer : _doPlayers) {
					_shanExtension.clientDo(doPlayer);
				}
				processWinLoseFinal();
			}
			else {
				processTurnChange();
			}
		}
	}
	
	public void onBankDo() {
		processWinLoseFinal();
	}
	
	public void processBankerCatch() {
		for (RoomPlayer skmPlayer : _players) {
			if(skmPlayer.hasThirdCard()) {
				decideWinLose(skmPlayer);
				calculateAndPayMoneyForSinglePlayer(skmPlayer);
			}
		}
	}
	
	public void processBankerCatchTwo() {
		for (RoomPlayer skmPlayer : _players) {
			if(!skmPlayer.hasThirdCard()) {
				decideWinLose(skmPlayer);
				calculateAndPayMoneyForSinglePlayer(skmPlayer);
			}
		}
	}
	
	public void processDecisionForCurrentPlayer() {
		_shanExtension.cancelDecisionTimer();
		
		if(!_currentPlayer.isBotA && !_currentPlayer.isBotB && _currentPlayer.getSFSUser() != null) {
			
			if(_currentPlayer.getHandValue() <= 4) {
				processClientDraw(_currentPlayer.getSFSUser());
			}
			else {
				processClientStand(_currentPlayer.getSFSUser());
			}
		}
		else {
			return;
		}
		
		//processTurnChange();
	}
	
	public void processBetDecisionForAll() {
		for (RoomPlayer roomPlayer : _players) {
			if(!roomPlayer.hasBet ) {
			 processClientBet(roomPlayer, 1000);
			}
			else {
				continue;
			}
		}
	}
	
	public void processTurnChange() {
    	_turnIndex++;
    	
    	_turnIndex = _turnIndex % _players.size();
    	
    	_currentPlayer = _players.get(_turnIndex);
    	if(_currentPlayer.isDo() || _currentPlayer.isPlayerLeft) {
    		processTurnChange();
    		return;
    	}
    	

    	if(_currentPlayer.isBotA || _currentPlayer.isBotB) {
    		_shanExtension.scheduleBotDecision(_currentPlayer);
    	}
    	else {
    		_shanExtension.startDecisionTimer();
    	}
    	_shanExtension.startPlayerTurn(_currentPlayer);
    	
    		
    }

	
	public void processWinLoseFinal() {
		_shanExtension.cancelDecisionTimer();
		_shanExtension.sendClientHandCardsToAll(getBanker());
		
		sendMatchEnd();
		//List<RoomPlayer> players = _players; 
		//players.remove(getBanker());
		
		List<RoomPlayer> players = new ArrayList<>(_players);
		players.remove(getBanker());

		
		if(getBanker().isDo()) {
			for (RoomPlayer skmPlayer : players) {
				if(skmPlayer.decidedWinLose) {
					continue;
				}
				if(skmPlayer.isDo()) {
					decideWinLose(skmPlayer);
				}
				else {
					//_shanExtension.clientLose(skmPlayer);
					_losePlayers.add(skmPlayer);
				}
			}
		}
		else {
			for (RoomPlayer skmPlayer : players) {
				if(skmPlayer.decidedWinLose) {
					continue;
				}
				if(skmPlayer.isDo()) {
					//_shanExtension.clientWin(skmPlayer);
					_winPlayers.add(skmPlayer);
				}
				else {
					decideWinLose(skmPlayer);
				}
			}
		}
		
		calculateAndPayMoneyForPlayers();
		
	}
	
	private void decideWinLose(RoomPlayer skmPlayer) {
		
		if(skmPlayer.getModifier() == 5 && getBanker().getModifier() < 5) {
			_winPlayers.add(skmPlayer);
			return;
		}
		
		if(getBanker().getModifier() == 5 && skmPlayer.getModifier() < 5) {
			_losePlayers.add(skmPlayer);
			return;
		}
		
		if(skmPlayer.getHandValue() > getBanker().getHandValue()) {
			//_shanExtension.clientWin(skmPlayer);
			_winPlayers.add(skmPlayer);
		}
		else if(skmPlayer.getHandValue() == getBanker().getHandValue()) {
			if(skmPlayer.getBiggestCard() > getBanker().getBiggestCard()) {
				//_shanExtension.clientWin(skmPlayer);
				_winPlayers.add(skmPlayer);
			}
			else if(skmPlayer.getBiggestCard() == getBanker().getBiggestCard()) {
				if(skmPlayer.getHighestSuit() > getBanker().getHighestSuit()) {
					//_shanExtension.clientWin(skmPlayer);
					_winPlayers.add(skmPlayer);
				}
				else {
					//_shanExtension.clientLose(skmPlayer);
					_losePlayers.add(skmPlayer);
				}
			}
			else {
				//_shanExtension.clientLose(skmPlayer);
				_losePlayers.add(skmPlayer);
			}
		}
		else {
			//_shanExtension.clientLose(skmPlayer);
			_losePlayers.add(skmPlayer);
		}
	}
	
	private void calculateAndPayMoneyForSinglePlayer(RoomPlayer roomPlayer) {
		if(roomPlayer.decidedWinLose) {
			return;
		}
		
	    int playerLoseMoney = 0;
	    
	    // Calculate money lost if player loses
	    if (_losePlayers.contains(roomPlayer)) {
	        playerLoseMoney = roomPlayer.payMoney(1);
	        _curBankAmount += playerLoseMoney;
	        _shanExtension.clientLose(roomPlayer);
	    }

	    // If the player is a winner, calculate money to pay
	    if (_winPlayers.contains(roomPlayer)) {
	        int moneyToPay = roomPlayer.moneyToReceive();
	        if (moneyToPay > _curBankAmount) {
	            moneyToPay = _curBankAmount;
	        }
	        
	        roomPlayer.receiveMoney(moneyToPay);
	        _curBankAmount -= moneyToPay;

	        // Check if bank balance is depleted
	        if (_curBankAmount <= 0) {
	            _changingBank = true;
	        }

	        _shanExtension.clientWin(roomPlayer);
	    }
	    
	    roomPlayer.decidedWinLose = true;
	}

	
	private void calculateAndPayMoneyForPlayers() {
		int playerLoseMoney = 0;
		//int playerWinMoney = 0;
		
		for (RoomPlayer roomPlayer : _losePlayers) {
			if(roomPlayer.decidedWinLose) {
				continue;
			}
			playerLoseMoney += roomPlayer.payMoney(getBanker().getModifier());
		}
		
		_curBankAmount += playerLoseMoney;
		
		sortPlayers(_winPlayers);
		
		for (RoomPlayer roomPlayer : _winPlayers) {
			if(roomPlayer.decidedWinLose) {
				continue;
			}
			int moneyToPay = roomPlayer.moneyToReceive();
			if(moneyToPay >= _curBankAmount) {
				moneyToPay = _curBankAmount;
			}
			
			roomPlayer.receiveMoney(moneyToPay);
			_curBankAmount -= moneyToPay;
			
			if(_curBankAmount <= 0) {
				_changingBank = true;
			}
		}
	
		//_curBankAmount -= playerWinMoney;
		
		for (RoomPlayer roomPlayer : _winPlayers) {
			if(roomPlayer.decidedWinLose) {
				continue;
			}
			_shanExtension.clientWin(roomPlayer);
		}
		
		for (RoomPlayer roomPlayer : _losePlayers) {
			if(roomPlayer.decidedWinLose) {
				continue;
			}
			_shanExtension.clientLose(roomPlayer);
		}
		
		sendTransitionToWebServerAsync().thenAccept(response -> {
		    if (response == null) {
		        _shanExtension.trace("Transaction API failed.");
		        return;
		    }
		    
		    for (TransactionPlayer data : response.data.players) {
		    	
				if(!data.player_id.equals("AG72360789")) {
					double balanceAsDouble = Double.parseDouble(data.current_balance.trim());
				    int balanceAsInt = (int) balanceAsDouble; // This will truncate decimal part
				    _shanExtension.trace("Parsed balance: " + balanceAsInt);
				    getPlayerByName(data.player_id).setTotalAmount(balanceAsInt);
					
					
				}
			}
		    
		    restartGameLoop();
		    // You can now update game state or UI here
		    _shanExtension.trace("Transaction completed successfully.");
		}).exceptionally(ex -> {
		    ex.printStackTrace();
		    return null;
		});
		
	}
	
	public void sortPlayers(ArrayList<RoomPlayer> players) {
        Collections.sort(players, new Comparator<RoomPlayer>() {
            @Override
            public int compare(RoomPlayer p1, RoomPlayer p2) {
                // Check if p1 isDo and p2 isDo
                if (p1.isDo() && !p2.isDo()) {
                    return -1; // p1 goes before p2
                } else if (!p1.isDo() && p2.isDo()) {
                    return 1; // p2 goes before p1
                } else if (p1.isDo() && p2.isDo()) {
                    // Both are isDo, compare hand value
                    int handValueComparison = Integer.compare(p2.getHandValue(), p1.getHandValue());
                    if (handValueComparison != 0) {
                        return handValueComparison; // Return the comparison result
                    }
                    // If hand values are equal, compare by suit
                    return Integer.compare(p2.getHighestSuit(), p1.getHighestSuit());
                }

                // If neither isDo, check for modifier
                if (p1.getModifier() == 5 && p2.getModifier() != 5) {
                    return -1; // p1 goes before p2
                } else if (p1.getModifier() != 5 && p2.getModifier() == 5) {
                    return 1; // p2 goes before p1
                } else if (p1.getModifier() == 5 && p2.getModifier() == 5) {
                    // Both have modifier 5, compare hand value
                    int handValueComparison = Integer.compare(p2.getHandValue(), p1.getHandValue());
                    if (handValueComparison != 0) {
                        return handValueComparison; // Return the comparison result
                    }
                    // If hand values are equal, compare by suit
                    return Integer.compare(p2.getHighestSuit(), p1.getHighestSuit());
                }

                // For the rest of the players, compare by hand value
                int handValueComparison = Integer.compare(p2.getHandValue(), p1.getHandValue());
                if (handValueComparison != 0) {
                    return handValueComparison; // Return the comparison result
                }
                // If hand values are equal, compare by suit
                return Integer.compare(p2.getHighestSuit(), p1.getHighestSuit());
            }
        });
    }
	
	private void restartGameLoop() {
		_isIdle = true;
		TaskHelper.startScheduleTask(_shanExtension, () -> startGame(), 7);
	}
	
	public boolean IsRoomIdle() {
		return _isIdle;
	}
	
	private Card drawCardFromDeck() {
		return _cardDeck.drawCard();
	}
	
	public void resetGame() {
		for (RoomPlayer roomPlayer : _playersToLeave) {
			_players.remove(roomPlayer);
		}

		for (RoomPlayer roomPlayer : _players) {
			roomPlayer.resetPlayerHand();
		}
		_doPlayers = new ArrayList<RoomPlayer>();
		_winPlayers = new ArrayList<RoomPlayer>();
		_losePlayers = new ArrayList<RoomPlayer>();
		_cardDeck = new Deck();
		_playerDoneIndex = 0;
		_playerBet = 0;
	}
	
	public CompletableFuture<GetBalanceResponse> getBalancesFromWebServerAsync() {
	    return CompletableFuture.supplyAsync(() -> {
	        try {
	            HttpPost request = new HttpPost(Constants.API_URI + Constants.API_GETBALANCE_ENDPOINT);

	            List<GetBalanceUser> users = new ArrayList<>();
	            for (RoomPlayer player : _players) {
	                if (!player.isBotA && !player.isBotB) {
	                    users.add(new GetBalanceUser(player.playerName));
	                }
	            }

	            GetBalanceReq getBalanceReq = new GetBalanceReq(users.toArray(new GetBalanceUser[0]));
	            ObjectMapper mapper = new ObjectMapper();
	            String jsonRequest = mapper.writeValueAsString(getBalanceReq);

	            _shanExtension.trace("get balance request : " + jsonRequest);
	            request.setEntity(new StringEntity(jsonRequest, ContentType.APPLICATION_JSON));

	            try (CloseableHttpResponse response = (CloseableHttpResponse) client.execute(request);
	                 BufferedReader rd = new BufferedReader(new InputStreamReader(response.getEntity().getContent()))) {

	                StringBuilder responseBuilder = new StringBuilder();
	                String line;
	                while ((line = rd.readLine()) != null) {
	                    responseBuilder.append(line);
	                }

	                String jsonResponse = responseBuilder.toString();
	                _shanExtension.trace("get balance response : " + jsonResponse);

	                GetBalanceResponse balanceResponse = mapper.readValue(jsonResponse, GetBalanceResponse.class);

	                for (BalanceData balanceData : balanceResponse.data) {
	                    _shanExtension.trace("Account: " + balanceData.member_account + ", Balance: " + balanceData.balance);
	                }

	                return balanceResponse;
	            }
	        } catch (Exception e) {
	            e.printStackTrace();
	            return null; // Optional: You can return an empty object or throw a runtime exception here.
	        }
	    });
	}

	
	public CompletableFuture<TransactionResponse> sendTransitionToWebServerAsync() {
	    return CompletableFuture.supplyAsync(() -> {
	        try {
	            HttpPost request = new HttpPost(Constants.API_URI + Constants.API_TRANSITION_ENDPOINT);

	            List<ResultPlayer> players = new ArrayList<>();

	            for (RoomPlayer roomPlayer : _winPlayers) {
	                if (!roomPlayer.isBanker() && !roomPlayer.isBotA && !roomPlayer.isBotB) {
	                    String userName = roomPlayer.playerName;
	                    players.add(new ResultPlayer(userName, roomPlayer.playerName, 1, roomPlayer.recentBetAmount  , roomPlayer.amountChanged  ));
	                } else if (!roomPlayer.isBanker() && (roomPlayer.isBotA || roomPlayer.isBotB)) {
	                    players.add(new ResultPlayer("SKP0101", "SKP0101", 1, roomPlayer.recentBetAmount  , roomPlayer.amountChanged  ));
	                }
	            }

	            for (RoomPlayer roomPlayer : _losePlayers) {
	                if (!roomPlayer.isBanker() && !roomPlayer.isBotA && !roomPlayer.isBotB) {
	                    String userName = roomPlayer.playerName;
	                    players.add(new ResultPlayer(userName, roomPlayer.playerName, 0, roomPlayer.recentBetAmount  , roomPlayer.amountChanged  ));
	                } else if (!roomPlayer.isBanker() && (roomPlayer.isBotA || roomPlayer.isBotB)) {
	                    players.add(new ResultPlayer("SKP0101", "SKP0101", 1, roomPlayer.recentBetAmount  , roomPlayer.amountChanged  ));
	                }
	            }

	            RoomPlayer bankPlayer = getBanker();
	            ResultBanker banker;

	            if (bankPlayer.isBotA || bankPlayer.isBotB) {
	                banker = new ResultBanker("SKP0101", "SKP0101", bankPlayer.getTotalAmount() + _curBankAmount);
	            } else {
	                String bankName = bankPlayer.playerName;
	                banker = new ResultBanker(bankName, bankPlayer.playerName, bankPlayer.getTotalAmount() + _curBankAmount);
	            }

	            TransitionReq transitionReq = new TransitionReq(players.toArray(new ResultPlayer[0]), banker);

	            ObjectMapper mapper = new ObjectMapper();
	            String jsonRequest = mapper.writeValueAsString(transitionReq);
	            _shanExtension.trace("transaction request : " + jsonRequest);

	            request.setHeader("X-Transaction-Key", "yYpfrVcWmkwxWx7um0TErYHj4YcHOOWr");
	            request.setEntity(new StringEntity(jsonRequest, ContentType.APPLICATION_JSON));

	            try (CloseableHttpResponse response = (CloseableHttpResponse) client.execute(request);
	                 BufferedReader rd = new BufferedReader(new InputStreamReader(response.getEntity().getContent()))) {

	                StringBuilder responseBuilder = new StringBuilder();
	                String line;
	                while ((line = rd.readLine()) != null) {
	                    responseBuilder.append(line);
	                }

	                String jsonResponse = responseBuilder.toString();
	                //_shanExtension.trace("transaction response : " + jsonResponse);

	                TransactionResponse transactionResponse = mapper.readValue(jsonResponse, TransactionResponse.class);

	                for (TransactionPlayer data : transactionResponse.data.players) {
	                    _shanExtension.trace("Player ID: " + data.player_id + ", Balance: " + data.current_balance);
	                }

	                return transactionResponse;
	            }
	        } catch (Exception e) {
	            e.printStackTrace();
	            return null; // Optional: You can return an empty object or throw a RuntimeException
	        }
	    });
	}

}
