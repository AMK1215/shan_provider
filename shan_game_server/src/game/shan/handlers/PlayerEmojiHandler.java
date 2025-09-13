package game.shan.handlers;

import com.smartfoxserver.v2.entities.User;
import com.smartfoxserver.v2.entities.data.ISFSObject;
import com.smartfoxserver.v2.extensions.BaseClientRequestHandler;

import game.shan.constant.Constants;
import game.shan.game.RoomPlayer;
import game.shan.game.SKMGame;
import game.shan.utils.RoomHelper;

public class PlayerEmojiHandler extends BaseClientRequestHandler {

	@Override
	public void handleClientRequest(User user, ISFSObject object) {
		// TODO Auto-generated method stub
		int index = object.getInt(Constants.INDEX);
		
		SKMGame game = RoomHelper.getGame(this);
		RoomPlayer player = game.getPlayerByUser(user);
		
		game.processClientEmoji(player, index);

	}

}
