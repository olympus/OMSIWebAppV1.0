<style>
.button {
  background-color: #4CAF50; /* Green */
  border: none;
  color: white;
  padding: 15px 32px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
}
</style>

<p>Dear Sir, 
  please find below customer call received through My Voice App to be assigned to FSE, 
  kindly assign the same.</p>

<a href="{{ url('/assign_request/'.$assign_request->token.'/edit') }}" class="button">Assign Now</a>


<p>This button is valid till {{ $assign_request->expired_at }}</p>
</br></br>
<p>Regards,</p>
<p>Olympus Medical Systems India</p>

<img src="{{ $message->embed($pathToImage) }}">
