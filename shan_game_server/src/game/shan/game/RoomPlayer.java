package game.shan.game;

import java.util.ArrayList;
import java.util.Iterator;
import java.util.List;import javax.swing.text.StyledEditorKit.BoldAction;

import com.smartfoxserver.v2.entities.User;

import game.shan.card.Card;
import game.shan.card.Suit;
import game.shan.utils.RoomHelper;

//test 
public class RoomPlayer {
	private User _sfsUser;
	private List<Card> _handCards;
	private boolean _isBanker;
	private int _handValue = 0;
	private int _totalAmount;
	private int _betAmount;
	private int _modifier = 1; 
	
	public boolean isBotA = false;
	public boolean isBotB = false;
	
	public boolean isPlayerLeft = false;
	
	public String playerName;
	public int playerID = -1;
	
	public int amountChanged = 0;
	public int recentBetAmount = 0;
	
	public boolean hasBet = false;
	public boolean decidedWinLose = false;

	public RoomPlayer(User sfsUser, boolean isBanker)
	{
		_sfsUser = sfsUser;
		_handCards = new ArrayList<Card>();
		this._isBanker = isBanker;
		playerName = sfsUser.getName();
		isBotA = false;
		isBotB = false;
		hasBet = false;
		decidedWinLose = false;
	}
	
	public RoomPlayer(String name, boolean isBotA, boolean isBanker)
	{
		makeBot(isBotA);
		_handCards = new ArrayList<Card>();
		this._isBanker = isBanker;
		playerName = name;
		hasBet = false;
		decidedWinLose = false;
	}
	
	public int getModifier() {
		return _modifier;
	}
	
	public void setTotalAmount(int totalAmount) {
		_totalAmount = totalAmount;
	}
	
	public void addToTotalAmount(int addAmount) {
		_totalAmount += addAmount;
	}
	
	public int getTotalAmount() {
		return _totalAmount;
	}
	
	public int getBetAmount() {
		return _betAmount;
	}
	
	public void bet(int totalBet) {
		hasBet = true;
		 _betAmount += totalBet;
		 _totalAmount -= totalBet;
	}
	
	public int payMoney(int modifier) {
		int tot = _betAmount * modifier;
		//_totalAmount -= tot;
		recentBetAmount = _betAmount;
		_betAmount = 0;
		amountChanged = tot;
		return tot;
	}
	
	public int moneyToReceive() {
		return _betAmount * _modifier;
	}
	
	public void receiveMoney(int amount) {
		_totalAmount += amount + _betAmount;
		recentBetAmount = _betAmount;
		_betAmount = 0;
		amountChanged = amount;

	}
	
	public void makeBot(Boolean isBotA) {
		if(isBotA){
			this.isBotA = true;
		}
		else {
			this.isBotB = true;
		}
	}
	
	public User getSFSUser() {
		return _sfsUser;
	}
	
	public void setSFSUser(User user) {
		_sfsUser = user;
	}
	
	public List<Card> getHandCards() {
		return _handCards;
	}
	
	public void addCard(Card card)
	{
		_handCards.add(card);
		
		_handValue = 0;

        for (Card handCard : _handCards) {
            _handValue += handCard.value;
        }
        _handValue %= 10;  // Hand value is modulo 10
        
        setModifier();
	}
	
	private void setModifier() {
		_modifier = 1;
		
		if(_handCards.size() == 2) {
        	if(_handCards.get(0).suit == _handCards.get(1).suit){
        		_modifier = 2;
        	}
        }
		else if(_handCards.size() == 3) {
			if (_handCards.get(0).valueName == _handCards.get(1).valueName && _handCards.get(1).valueName == _handCards.get(2).valueName) {
			    _modifier = 5;
			}
			if (_handCards.get(0).suit == _handCards.get(1).suit && _handCards.get(1).suit == _handCards.get(2).suit) {
			    _modifier = 3;
			}
        }
		else {
			return;
		}
	}

	public void clearHand() {
		_handCards.clear();
	}
	
	public int getHandValue() {
		return _handValue;
    }
	
	public void setBankerOrNot(boolean isBanker) {
		_isBanker = isBanker;
	}
	
	public boolean isBanker() {
		return _isBanker;
	}
	
	public boolean hasThirdCard() {
		return _handCards.size() == 3;
	}
	
	public boolean isDo() //auto do
	{
		return (_handCards.size() == 2 && (getHandValue() == 8 || getHandValue() == 9));
	}
	
	public void drawCard(Card drawnCard) {
		addCard(drawnCard);
	}
	
	public List<String> getHandCardNameArray() {
		List<String> name = new ArrayList<String>();
		
		for (Card card : _handCards) {
			name.add(card.cardName);
		}
		
		return name;
	}
	
	public int getBiggestCard() {
	    int biggestValue = 0;

	    for (Card card : _handCards) {
	        if (card.value == 1) {
	            return 100; // Priority for card with value 1 Ace
	        }
	        if (card.value > biggestValue) {
	            biggestValue = card.value;
	        }
	    }

	    return biggestValue;
	}
	
	public int getHighestSuit() {
		Suit highestSuit = Suit.Clubs;
		
		for (Card card : _handCards) {
			if(card.suit.ordinal() > highestSuit.ordinal()) {
				highestSuit = card.suit;
			}
		}
		
		return highestSuit.ordinal();
	}

	public void resetPlayerHand() {
		_handCards = new ArrayList<Card>();
		isPlayerLeft = false;
		hasBet = false;
		decidedWinLose = false;
		_modifier = 1;
	}
}
