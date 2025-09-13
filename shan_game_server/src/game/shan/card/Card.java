package game.shan.card;

import com.smartfoxserver.v2.entities.data.ISFSObject;
import com.smartfoxserver.v2.entities.data.SFSObject;

import game.shan.constant.Constants;

public class Card {
	public String cardName;
	public int value;
	public Suit suit;
	public boolean isAce;
	public String valueName;
	
	
	public Card(String name, int value, String valueName, Suit suit)
	{
		this.cardName = name;
		this.value = value;
		this.suit = suit;
		this.valueName = valueName;
		this.isAce = (value == 1);
	}
	
	public ISFSObject toSFSObject() {
		ISFSObject data = new SFSObject();
		data.putUtfString(Constants.CARD_NAME, cardName);
		data.putInt(Constants.CARD_VALUE, value);
		data.putInt(Constants.SUIT, suit.ordinal());
		data.putBool(Constants.IS_ACE, isAce);
		
		return data;
	}
}
