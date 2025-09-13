package game.shan;

import java.util.ArrayList;
import java.util.Iterator;
import java.util.List;
import java.util.Random;
import java.util.concurrent.ScheduledFuture;
import java.util.concurrent.TimeUnit;

import com.smartfoxserver.v2.SmartFoxServer;
import com.smartfoxserver.v2.core.SFSEventType;
import com.smartfoxserver.v2.entities.Room;
import com.smartfoxserver.v2.entities.User;
import com.smartfoxserver.v2.entities.data.ISFSArray;
import com.smartfoxserver.v2.entities.data.ISFSObject;
import com.smartfoxserver.v2.entities.data.SFSArray;
import com.smartfoxserver.v2.entities.data.SFSObject;
import com.smartfoxserver.v2.exceptions.SFSJoinRoomException;
import com.smartfoxserver.v2.exceptions.SFSLoginException;
import com.smartfoxserver.v2.extensions.ExtensionLogLevel;
import com.smartfoxserver.v2.extensions.SFSExtension;
import com.smartfoxserver.v2.util.TaskScheduler;

import game.shan.card.Card;
import game.shan.constant.Constants;
import game.shan.game.SKMGame;
import game.shan.game.RoomPlayer;
import game.shan.handlers.BankerCatchHandler;
import game.shan.handlers.BankerCatchTwoHandler;
import game.shan.handlers.PlayerBetHandler;
import game.shan.handlers.PlayerDrawCardHandler;
import game.shan.handlers.PlayerEmojiHandler;
import game.shan.handlers.PlayerJoinEventHandler;
import game.shan.handlers.PlayerLeaveEventHandler;
import game.shan.handlers.PlayerMessageHandler;
import game.shan.handlers.PlayerStandHandler;
import game.shan.handlers.StartGameHandler;
import game.shan.utils.RoomHelper;
import game.shan.utils.TaskHelper;
import game.shan.utils.UserHelper;

public class ShanExtension extends SFSExtension {
	
	private ScheduledFuture<?> _countdownTask;
	private ScheduledFuture<?> _startGameTask;
	private ScheduledFuture<?> _ownerTask;
	private ScheduledFuture<?> _betDecisionTask;
	private ScheduledFuture<?> _winLoseFinalTask;
	private ScheduledFuture<?> _turnChangeTask;
	private ScheduledFuture<?> _createBotsTask;
	private ScheduledFuture<?> _botDecisionTask;
	private int _countdownSecs = 0;

	private SKMGame _shanGame;
	private TaskScheduler _taskScheduler;
	
	@Override
	public void init() {
		// TODO Auto-generated method stub
		_taskScheduler = SmartFoxServer.getInstance().getTaskScheduler();
		_shanGame = new SKMGame(this);
		
		ListenEvents();
	}
	
	@Override
    public void destroy() {
		removeEvents();
		this._shanGame = null;
		this._taskScheduler = null;
        super.destroy();
    }
	
	private void ListenEvents() {
		addRequestHandler(Constants.STARTGAME, StartGameHandler.class);
		addRequestHandler(Constants.DRAW_CARD, PlayerDrawCardHandler.class);
		addRequestHandler(Constants.STAND, PlayerStandHandler.class);
		addRequestHandler(Constants.BET, PlayerBetHandler.class);
		addRequestHandler(Constants.PLAYER_SENT_MESSAGE, PlayerMessageHandler.class);
		addRequestHandler(Constants.PLAYER_SENT_EMOJI, PlayerEmojiHandler.class);
		addRequestHandler(Constants.BANK_CATCH, BankerCatchHandler.class);
		addRequestHandler(Constants.BANK_CATCH_TWO, BankerCatchTwoHandler.class);
		
		addEventHandler(SFSEventType.USER_JOIN_ROOM, PlayerJoinEventHandler.class);
		addEventHandler(SFSEventType.USER_LEAVE_ROOM, PlayerLeaveEventHandler.class);
		addEventHandler(SFSEventType.USER_DISCONNECT, PlayerLeaveEventHandler.class);
	}
	
	public void removeEvents() {
		// Remove request handlers
	    removeRequestHandler(Constants.STARTGAME);
	    removeRequestHandler(Constants.DRAW_CARD);
	    removeRequestHandler(Constants.STAND);
	    removeRequestHandler(Constants.BET);
	    removeRequestHandler(Constants.PLAYER_SENT_MESSAGE);
	    removeRequestHandler(Constants.PLAYER_SENT_EMOJI);
	    removeRequestHandler(Constants.BANK_CATCH);
	    removeRequestHandler(Constants.BANK_CATCH_TWO);
	    
	    // Remove event handlers
	    removeEventHandler(SFSEventType.USER_JOIN_ROOM);
	    removeEventHandler(SFSEventType.USER_LEAVE_ROOM);
	    removeEventHandler(SFSEventType.USER_DISCONNECT);
	}

	public TaskScheduler getTaskScheduler() {
		return _taskScheduler;
	}
	
	public SKMGame getGame() {
		return _shanGame;
	}
	

	
	public void scheduleBeforeStartGame() { //schedule and send

		TaskHelper.startScheduleTask(this, () -> _shanGame.startGame(), 5);
	}
	
	public void scheduleSendOwner(User owner, boolean sendToOwner) { //schedule and send
		TaskHelper.startScheduleTask(this, _ownerTask, () -> sendOwner(owner, sendToOwner), 2);
	}
	
	public void sendOwner(User owner, boolean sendToOwner) {
		SFSObject sFSObject = new SFSObject();
		sFSObject.putUtfString(Constants.USER_NAME, owner.getName());
		Room currentRoom = RoomHelper.getCurrentRoom(this);
	    List<User> userList = UserHelper.getRecipientsList(currentRoom);
	    if(!sendToOwner) {
	    	userList.remove(owner);
	    }
	    
	    send(Constants.OWNER, (ISFSObject)sFSObject, userList);
	}
	
	public void sendBanker(RoomPlayer banker) {
		SFSObject sFSObject = new SFSObject();
		sFSObject.putUtfString(Constants.USER_NAME, banker.playerName);
		sFSObject.putInt(Constants.TOTAL_AMOUNT, banker.getTotalAmount());
		sFSObject.putInt(Constants.BANK_AMOUNT, _shanGame.getBankAmount());
		sFSObject.putBool(Constants.IS_WARNING, _shanGame.isWarning);
		Room currentRoom = RoomHelper.getCurrentRoom(this);
	    List<User> userList = UserHelper.getRecipientsList(currentRoom);
	    
	    for (User user : _shanGame._usersWaiting) {
			userList.remove(user);
		}
	    
	    send(Constants.BANKER, (ISFSObject)sFSObject, userList);
	}

	public void sendClientEmojiToRoom(RoomPlayer sender, int index) {
		SFSObject sFSObject = new SFSObject();
		sFSObject.putUtfString(Constants.USER_NAME, sender.playerName);
		sFSObject.putInt(Constants.INDEX, index);
		Room currentRoom = RoomHelper.getCurrentRoom(this);
	    List<User> userList = UserHelper.getRecipientsList(currentRoom);
	    
	    for (User user : _shanGame._usersWaiting) {
			userList.remove(user);
		}
	    
	    send(Constants.PLAYER_EMOJI, (ISFSObject)sFSObject, userList);
	}
	
	public void sendClientMessageToRoom(RoomPlayer sender, String messageString) {
		SFSObject sFSObject = new SFSObject();
		sFSObject.putUtfString(Constants.USER_NAME, sender.playerName);
		sFSObject.putUtfString(Constants.MESSAGE_STRING, messageString);
		Room currentRoom = RoomHelper.getCurrentRoom(this);
	    List<User> userList = UserHelper.getRecipientsList(currentRoom);
	    
	    for (User user : _shanGame._usersWaiting) {
			userList.remove(user);
		}
	    
	    send(Constants.PLAYER_MESSAGE, (ISFSObject)sFSObject, userList);
	}
	
	public void scheduleStartGame() { //schedule and send countdown to players
		cancelDecisionTimer();
		_shanGame.processingWinLose = false;
		TaskHelper.startScheduleTask(this, () -> startBet(), Constants.GAME_START_DELAY);
		startCountdownTask(10, 1);
	}
	
	public void startCountdownTask(int duration, int interval)
	{
		_countdownSecs = duration;
		String cdEventName = Constants.COUNTDOWN;
		
		_countdownTask = getTaskScheduler().scheduleAtFixedRate(new Runnable() {
            @Override
            public void run() {
                ISFSObject object = new SFSObject();
                object.putInt("countdown", _countdownSecs);
                List<User> users = UserHelper.getRecipientsList(getParentRoom());
                
                send(cdEventName, object, users);
                
                _countdownSecs--;

                if (_countdownSecs <= 0) {
                    TaskHelper.cancelTask(_countdownTask);
                    _countdownSecs = 0;
                }
            }
        }, 0, interval, TimeUnit.SECONDS); // Run every 'interval' seconds
	}
	
	public void scheduleCreateBots(Room room) { //schedule and send countdown to players
		TaskHelper.startScheduleTask(this, _createBotsTask, () -> createBots(room), 1);
	}
	
	public void createBots(Room room) {
		getGame().addBots(room);
	}
	
	public void scheduleAutoBet(RoomPlayer better) { //schedule and send
		if(!better.hasBet) {
			if(better.isBotA || better.isBotB) {
				Random random = new Random();
				int randomNum = random.nextInt(4) + 3;
				
				TaskHelper.startScheduleTask(this, () -> makeAutoBet(better), randomNum);
			}
			else {
				TaskHelper.startScheduleTask(this, () -> makeAutoBet(better), 10);
			}
		}
		else {
			return;
		}
		
	}
	
	public void makeAutoBet(RoomPlayer better) {
		
		if(better.hasBet) {
			return;
		}
		getGame().processClientBet(better); //temp
	}
	
	public void scheduleBotDecision(RoomPlayer bot) { //schedule and send
		TaskHelper.startScheduleTask(this, _botDecisionTask, () -> makeBotDecision(bot), 3);
	}
	
	public void makeBotDecision(RoomPlayer bot) {
		getGame().processBotDecision(bot);
	}
	
	
	
    private void makeAutoDecisionForCurrentPlayer() {
		_shanGame.processDecisionForCurrentPlayer();
	}
    
    public void startBet() { //send game start event
		SFSObject sFSObject = new SFSObject();
		sFSObject.putInt(Constants.BANK_AMOUNT, _shanGame.getBankAmount());
		sFSObject.putInt(Constants.MIN_BET, getParentRoom().getVariable(Constants.MIN_BET).getIntValue());
	    Room currentRoom = RoomHelper.getCurrentRoom(this);
	    List<User> userList = UserHelper.getRecipientsList(currentRoom);
	    
	    for (User user : _shanGame._usersWaiting) {
			userList.remove(user);
		}

	    send(Constants.BET_STARTED, (ISFSObject)sFSObject, userList);
	    
	    _shanGame.onBetStarted();
	}
    
    public void scheduleBetStateDecisions() {
		TaskHelper.startScheduleTask(this, _betDecisionTask, () -> makeAutoBetDecisionForAll(), 10);

	}
    
    private void makeAutoBetDecisionForAll() {
		_shanGame.processBetDecisionForAll();
	}
    
    public void startDecisionTimer() {
        // Cancel the current decision timer (if any)
        cancelDecisionTimer();

        // Start a new decision timer
        TaskHelper.startScheduleTask(this, _turnChangeTask, () -> makeAutoDecisionForCurrentPlayer(), 12);
    }

    public void cancelDecisionTimer() {
        if (_turnChangeTask != null && !_turnChangeTask.isCancelled()) {
            _turnChangeTask.cancel(true);  // Cancel the task
            _turnChangeTask = null;        // Reset the reference
        }
    }
    
	
	public void startGame() { //send game start event
		trace("starting game");
		SFSObject sFSObject = new SFSObject();
		
	    Room currentRoom = RoomHelper.getCurrentRoom(this);
	    
	    List<User> userList = UserHelper.getRecipientsList(currentRoom);
	    
	    for (User user : _shanGame._usersWaiting) {
			userList.remove(user);
		}
	    
	    send(Constants.GAME_STARTED, (ISFSObject)sFSObject, userList);
	    _shanGame.onGameStarts();
	    
	    //nextTurn();
	    //scheduleTurnLoop();
	}
	
	public void startPlayerTurn(RoomPlayer player) {
    	
    	/*if(player.isBotA || player.isBotB || player.getSFSUser() == null) {
			return;
		}*/
    	
    	ISFSObject sFSObject = new SFSObject();
    	sFSObject.putUtfString(Constants.USER_NAME, player.playerName);
    	Room currentRoom = RoomHelper.getCurrentRoom(this);
	    List<User> userList = UserHelper.getRecipientsList(currentRoom);
    	
//	    for (User user : _shanGame._usersWaiting) {
//			userList.remove(user);
//		}
	    
	    send(Constants.START_CURRENT_TURN, sFSObject, userList);
	}
	
	/*
	 * public void startBankerTurn(SKMPlayer banker) {
	 * trace(banker.getSFSUser().getName() + "'s turn.."); ISFSObject sFSObject =
	 * new SFSObject(); sFSObject.putUtfString(Constants.USER_NAME,
	 * banker.getSFSUser().getName()); Room currentRoom =
	 * RoomHelper.getCurrentRoom(this); List<User> userList =
	 * UserHelper.getRecipientsList(currentRoom); send(Constants.START_CURRENT_TURN,
	 * sFSObject, userList); }
	 */
	
	public void sendBotJoined(RoomPlayer bot) {
    	ISFSObject sFSObject = new SFSObject();
    	sFSObject.putUtfString(Constants.USER_NAME, bot.playerName);
	    Room currentRoom = RoomHelper.getCurrentRoom(this);
	    send(Constants.BOT_JOINED, sFSObject, currentRoom.getOwner());
	}
	
	/*
	 * public void sendRoomPlayerList(ArrayList<RoomPlayer> playerList) {
	 * ArrayList<String> nameArrStrings = new ArrayList<String>();
	 * 
	 * for (String string : nameArrStrings) { trace(string); }
	 * 
	 * for (RoomPlayer player : playerList) { nameArrStrings.add(player.playerName);
	 * }
	 * 
	 * ISFSObject sFSObject = new SFSObject();
	 * sFSObject.putUtfStringArray(Constants.USER_NAME_ARRAY, nameArrStrings);
	 * 
	 * Room currentRoom = RoomHelper.getCurrentRoom(this); List<User> userList =
	 * UserHelper.getRecipientsList(currentRoom);
	 * //userList.remove(currentRoom.getOwner());
	 * 
	 * send(Constants.ROOM_PLAYER_LIST, (ISFSObject)sFSObject, userList); }
	 */
	
	public void sendRoomPlayerList(ArrayList<RoomPlayer> playerList) {
		ISFSArray playersArray = new SFSArray();
		
		for (RoomPlayer roomPlayer : playerList) {
			ISFSObject playerData = new SFSObject();
			
			playerData.putUtfString(Constants.USER_NAME, roomPlayer.playerName);
			playerData.putInt(Constants.TOTAL_AMOUNT, roomPlayer.getTotalAmount());
			
			playersArray.addSFSObject(playerData);
			}
		
		ISFSObject sFSObject = new SFSObject();
    	sFSObject.putSFSArray(Constants.USER_ARRAY, playersArray);
    	
    	Room currentRoom = RoomHelper.getCurrentRoom(this);
	    List<User> userList = UserHelper.getRecipientsList(currentRoom);
	    //userList.remove(currentRoom.getOwner());
	    
	    for (User user : _shanGame._usersWaiting) {
			userList.remove(user);
		}
	    
	    send(Constants.ROOM_PLAYER_LIST, (ISFSObject)sFSObject, userList);
	}
	
	public void sendMatchEnd(int bankAmountChanged, Boolean isBankWin) {
		ISFSObject sFSObject = new SFSObject();
		sFSObject.putInt(Constants.AMOUNT_CHANGED, bankAmountChanged);
		sFSObject.putBool(Constants.IS_BANK_WIN, isBankWin);
    	
    	Room currentRoom = RoomHelper.getCurrentRoom(this);
	    List<User> userList = UserHelper.getRecipientsList(currentRoom);
	    //userList.remove(currentRoom.getOwner());
	    
	    for (User user : _shanGame._usersWaiting) {
			userList.remove(user);
		}
	    
	    send(Constants.MATCH_END, sFSObject, userList);
	}
	
	public void sendBetInfo(RoomPlayer better, int amount) {
		trace(better.playerName + " bettereerererer");
    	ISFSObject sFSObject = new SFSObject();
    	sFSObject.putUtfString(Constants.USER_NAME, better.playerName);
    	sFSObject.putInt(Constants.BET_AMOUNT, amount);
    	sFSObject.putInt(Constants.TOTAL_AMOUNT, better.getTotalAmount());
    	Room currentRoom = RoomHelper.getCurrentRoom(this);
	    List<User> userList = UserHelper.getRecipientsList(currentRoom);
	    
	    for (User user : _shanGame._usersWaiting) {
			userList.remove(user);
		}
	    
	    send(Constants.PLAYER_BET, sFSObject, userList);
	}
	
	public void sendClientHandCards(RoomPlayer player) {
		if(!player.isBotA && !player.isBotB) {
			ISFSObject sFSObject = new SFSObject();
	    	sFSObject.putUtfString(Constants.USER_NAME, player.playerName);
	    	sFSObject.putUtfStringArray(Constants.PLAYER_CARD_ARRAY, player.getHandCardNameArray());
	    	sFSObject.putInt(Constants.TOTAL_VALUE, player.getHandValue());
	    	sFSObject.putInt(Constants.MODIFIER, player.getModifier());
	    	sFSObject.putBool(Constants.IS_DO, player.isDo());
	    	
	    	send(Constants.PLAYER_HAND_CARDS, sFSObject, player.getSFSUser());
		}
		
    	
	}
	
	public void sendClientHandCardsToAll(RoomPlayer player) {
    	ISFSObject sFSObject = new SFSObject();
    	sFSObject.putUtfString(Constants.USER_NAME, player.playerName);
    	sFSObject.putUtfStringArray(Constants.PLAYER_CARD_ARRAY, player.getHandCardNameArray());
    	sFSObject.putInt(Constants.TOTAL_VALUE, player.getHandValue());
    	sFSObject.putInt(Constants.MODIFIER, player.getModifier());
    	sFSObject.putBool(Constants.IS_DO, player.isDo());
    	
    	Room currentRoom = RoomHelper.getCurrentRoom(this);
	    List<User> userList = UserHelper.getRecipientsList(currentRoom);
	    
	    for (User user : _shanGame._usersWaiting) {
			userList.remove(user);
		}
	    
	    if(!player.isBotA && !player.isBotB)
	    {
	    	userList.remove(player.getSFSUser());
	    }
    	send(Constants.PLAYER_HAND_CARDS, sFSObject, userList);
	}
	
	public void clientDrawCard(RoomPlayer player, Card drawnCard) {
		ISFSObject sFSObject = new SFSObject();

		if(!player.isBotA && !player.isBotB) {
			User drawer = player.getSFSUser();
	    	sFSObject.putUtfString(Constants.USER_NAME, player.playerName);
		    Room currentRoom = RoomHelper.getCurrentRoom(this);
	    	List<User> userList = UserHelper.getRecipientsList(currentRoom, drawer);
//	    	for (User user : _shanGame._usersWaiting) {
//				userList.remove(user);
//			}
	    	
	    	send(Constants.PLAYER_DRAW, sFSObject, userList);
	    	
	    	sFSObject = new SFSObject();
		    sFSObject.putUtfString(Constants.CARD_NAME, drawnCard.cardName);
		    sFSObject.putUtfString(Constants.USER_NAME, player.playerName);
		    sFSObject.putInt(Constants.TOTAL_VALUE, player.getHandValue());
		    sFSObject.putInt(Constants.MODIFIER, player.getModifier());
		    send(Constants.PLAYER_DRAW, sFSObject, drawer);
		}
		else {
	    	sFSObject.putUtfString(Constants.USER_NAME, player.playerName);
		    Room currentRoom = RoomHelper.getCurrentRoom(this);
	    	List<User> userList = UserHelper.getRecipientsList(currentRoom);
//	    	for (User user : _shanGame._usersWaiting) {
//				userList.remove(user);
//			}
	    	send(Constants.PLAYER_DRAW, sFSObject, userList);
		}
	    
	    
	}
	
	public void clientCaughtByBanker() {
		
	}
	
	/*
	 * public void delaySendDo(SKMPlayer player) {
	 * TaskHelper.startScheduleTask(this, _doTask, () -> clientDo(player), 2); }
	 */
	
	public void clientDo(RoomPlayer player) {
		ISFSObject sFSObject = new SFSObject();
		sFSObject.putUtfStringArray(Constants.PLAYER_CARD_ARRAY, player.getHandCardNameArray());
		sFSObject.putInt(Constants.TOTAL_VALUE, player.getHandValue());
		sFSObject.putUtfString(Constants.USER_NAME, player.playerName);
		
		Room currentRoom = RoomHelper.getCurrentRoom(this);
	    List<User> userList = UserHelper.getRecipientsList(currentRoom);
	    for (User user : _shanGame._usersWaiting) {
			userList.remove(user);
		}
	    
	    send(Constants.PLAYER_DO, sFSObject, userList);
	}
	
	public void clientWin(RoomPlayer player) {
		ISFSObject sFSObject = new SFSObject();
		sFSObject.putUtfStringArray(Constants.PLAYER_CARD_ARRAY, player.getHandCardNameArray());
		sFSObject.putUtfString(Constants.USER_NAME, player.playerName);
	    sFSObject.putInt(Constants.TOTAL_VALUE, player.getHandValue());
	    sFSObject.putInt(Constants.TOTAL_AMOUNT, player.getTotalAmount());
	    sFSObject.putInt(Constants.BANK_AMOUNT, _shanGame.getBankAmount());
	    sFSObject.putInt(Constants.MODIFIER, player.getModifier());
	    sFSObject.putBool(Constants.IS_DO, player.isDo());
	    sFSObject.putInt(Constants.AMOUNT_CHANGED, player.amountChanged);
	    
	    Room currentRoom = RoomHelper.getCurrentRoom(this);
	    List<User> userList = UserHelper.getRecipientsList(currentRoom);
	    
	    for (User user : _shanGame._usersWaiting) {
			userList.remove(user);
		}
	    
	    send(Constants.PLAYER_WIN, sFSObject, userList);
		
	}
	
	public void clientLose(RoomPlayer player) {
		ISFSObject sFSObject = new SFSObject();
		sFSObject.putUtfStringArray(Constants.PLAYER_CARD_ARRAY, player.getHandCardNameArray());
		sFSObject.putUtfString(Constants.USER_NAME, player.playerName);
	    sFSObject.putInt(Constants.TOTAL_VALUE, player.getHandValue());
	    sFSObject.putInt(Constants.TOTAL_AMOUNT, player.getTotalAmount());
	    sFSObject.putInt(Constants.BANK_AMOUNT, _shanGame.getBankAmount());
	    sFSObject.putInt(Constants.MODIFIER, player.getModifier());
	    sFSObject.putBool(Constants.IS_DO, player.isDo());
	    sFSObject.putInt(Constants.AMOUNT_CHANGED, player.amountChanged);
	    
	    Room currentRoom = RoomHelper.getCurrentRoom(this);
	    List<User> userList = UserHelper.getRecipientsList(currentRoom);
	    
	    for (User user : _shanGame._usersWaiting) {
			userList.remove(user);
		}
	    
	    send(Constants.PLAYER_LOSE, sFSObject, userList);
		
	}
	
	public void pleaseWait(User player) {
		ISFSObject sFSObject = new SFSObject();
	    
	    send(Constants.PLEASE_WAIT, sFSObject, player);
	}
	
	public void scheduldFinalWinLose() {
		cancelDecisionTimer();
		_shanGame.processingWinLose = true;
		TaskHelper.startScheduleTask(this, _winLoseFinalTask, () -> finalWinLose(), 7);
	}
	
	public void finalWinLose() {
		_shanGame.processWinLoseFinal();
	}
}
