package game.shan.serializeclasses;

public class GetBalanceReq {
	public GetBalanceUser[] batch_requests;
	public String currency;
	
	public GetBalanceReq (GetBalanceUser[] users) {
		batch_requests = users;
		currency = "MMK";
	}
}
