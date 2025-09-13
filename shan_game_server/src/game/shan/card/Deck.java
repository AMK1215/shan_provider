package game.shan.card;

import java.util.ArrayList;
import java.util.List;

import game.shan.constant.Constants;

import java.util.Collections;

public class Deck {
	private List<Card> _cardDeck;
	
	public Deck()
	{
		_cardDeck = new ArrayList<Card>();
		
		Initialize();
	}
	
	public void Initialize() {
		_cardDeck = GetFullDeckCards();
	}
	
	// Method to shuffle the deck
    public void shuffleDeck() {
        Collections.shuffle(_cardDeck);
    }

    // Method to add a card to the deck (for testing)
    public void addCard(Card card) {
        _cardDeck.add(card);
    }

    // Method to draw a card from the deck (for testing)
    public Card drawCard() {
        if (_cardDeck.isEmpty()) {
            return null; // or throw an exception, based on your preference
        }
        return _cardDeck.remove(0);
    }
    
    public Card viewCardByIndex(int index) {
		return _cardDeck.get(index);
	}
    
 // Method to draw a card from the deck (for testing)
    public Card drawDesireCardByIndex(int index) {
    	if (_cardDeck.isEmpty()) {
            return null; // or throw an exception, based on your preference
        }
        return _cardDeck.remove(index);
    }
    
    public List<Card> getCardDeck() {
		return _cardDeck;
	}
    
    private ArrayList<Card> GetFullDeckCards()
    {
    	ArrayList<Card> fullDeck = new ArrayList<>();

    	// Adding all club cards
    	fullDeck.add(new Card(Constants.CLUB_ACE, 1, "1", Suit.Clubs));
    	fullDeck.add(new Card(Constants.CLUB_TWO, 2, "2", Suit.Clubs));
    	fullDeck.add(new Card(Constants.CLUB_THREE, 3, "3", Suit.Clubs));
    	fullDeck.add(new Card(Constants.CLUB_FOUR, 4, "4", Suit.Clubs));
    	fullDeck.add(new Card(Constants.CLUB_FIVE, 5, "5", Suit.Clubs));
    	fullDeck.add(new Card(Constants.CLUB_SIX, 6, "6", Suit.Clubs));
    	fullDeck.add(new Card(Constants.CLUB_SEVEN, 7, "7", Suit.Clubs));
    	fullDeck.add(new Card(Constants.CLUB_EIGHT, 8, "8", Suit.Clubs));
    	fullDeck.add(new Card(Constants.CLUB_NINE, 9, "9", Suit.Clubs));
    	fullDeck.add(new Card(Constants.CLUB_TEN, 10, "10", Suit.Clubs));
    	fullDeck.add(new Card(Constants.CLUB_JACK, 10, "Jack", Suit.Clubs));
    	fullDeck.add(new Card(Constants.CLUB_QUEEN, 10, "Queen", Suit.Clubs));
    	fullDeck.add(new Card(Constants.CLUB_KING, 10, "King", Suit.Clubs));

    	// Adding all diamond cards
    	fullDeck.add(new Card(Constants.DIAMOND_ACE, 1, "1", Suit.Diamonds));
    	fullDeck.add(new Card(Constants.DIAMOND_TWO, 2, "2", Suit.Diamonds));
    	fullDeck.add(new Card(Constants.DIAMOND_THREE, 3, "3", Suit.Diamonds));
    	fullDeck.add(new Card(Constants.DIAMOND_FOUR, 4, "4", Suit.Diamonds));
    	fullDeck.add(new Card(Constants.DIAMOND_FIVE, 5, "5", Suit.Diamonds));
    	fullDeck.add(new Card(Constants.DIAMOND_SIX, 6, "6", Suit.Diamonds));
    	fullDeck.add(new Card(Constants.DIAMOND_SEVEN, 7, "7", Suit.Diamonds));
    	fullDeck.add(new Card(Constants.DIAMOND_EIGHT, 8, "8", Suit.Diamonds));
    	fullDeck.add(new Card(Constants.DIAMOND_NINE, 9, "9", Suit.Diamonds));
    	fullDeck.add(new Card(Constants.DIAMOND_TEN, 10, "10", Suit.Diamonds));
    	fullDeck.add(new Card(Constants.DIAMOND_JACK, 10, "Jack", Suit.Diamonds));
    	fullDeck.add(new Card(Constants.DIAMOND_QUEEN, 10, "Queen", Suit.Diamonds));
    	fullDeck.add(new Card(Constants.DIAMOND_KING, 10, "King", Suit.Diamonds));

    	// Adding all heart cards
    	fullDeck.add(new Card(Constants.HEART_ACE, 1, "1", Suit.Hearts));
    	fullDeck.add(new Card(Constants.HEART_TWO, 2, "2", Suit.Hearts));
    	fullDeck.add(new Card(Constants.HEART_THREE, 3, "3", Suit.Hearts));
    	fullDeck.add(new Card(Constants.HEART_FOUR, 4, "4", Suit.Hearts));
    	fullDeck.add(new Card(Constants.HEART_FIVE, 5, "5", Suit.Hearts));
    	fullDeck.add(new Card(Constants.HEART_SIX, 6, "6", Suit.Hearts));
    	fullDeck.add(new Card(Constants.HEART_SEVEN, 7, "7", Suit.Hearts));
    	fullDeck.add(new Card(Constants.HEART_EIGHT, 8, "8", Suit.Hearts));
    	fullDeck.add(new Card(Constants.HEART_NINE, 9, "9", Suit.Hearts));
    	fullDeck.add(new Card(Constants.HEART_TEN, 10, "10", Suit.Hearts));
    	fullDeck.add(new Card(Constants.HEART_JACK, 10, "Jack", Suit.Hearts));
    	fullDeck.add(new Card(Constants.HEART_QUEEN, 10, "Queen", Suit.Hearts));
    	fullDeck.add(new Card(Constants.HEART_KING, 10, "King", Suit.Hearts));

    	// Adding all spade cards
    	fullDeck.add(new Card(Constants.SPADE_ACE, 1, "1", Suit.Spades));
    	fullDeck.add(new Card(Constants.SPADE_TWO, 2, "2", Suit.Spades));
    	fullDeck.add(new Card(Constants.SPADE_THREE, 3, "3", Suit.Spades));
    	fullDeck.add(new Card(Constants.SPADE_FOUR, 4, "4", Suit.Spades));
    	fullDeck.add(new Card(Constants.SPADE_FIVE, 5, "5", Suit.Spades));
    	fullDeck.add(new Card(Constants.SPADE_SIX, 6, "6", Suit.Spades));
    	fullDeck.add(new Card(Constants.SPADE_SEVEN, 7, "7", Suit.Spades));
    	fullDeck.add(new Card(Constants.SPADE_EIGHT, 8, "8", Suit.Spades));
    	fullDeck.add(new Card(Constants.SPADE_NINE, 9, "9", Suit.Spades));
    	fullDeck.add(new Card(Constants.SPADE_TEN, 10, "10", Suit.Spades));
    	fullDeck.add(new Card(Constants.SPADE_JACK, 10, "Jack", Suit.Spades));
    	fullDeck.add(new Card(Constants.SPADE_QUEEN, 10, "Queen", Suit.Spades));
    	fullDeck.add(new Card(Constants.SPADE_KING, 10, "King", Suit.Spades));

	    
	    
	    return fullDeck;
    }
}
