document.addEventListener("DOMContentLoaded", () => { // Espera a página carregar antes de executar o código

  const produtos = [ // Cria a lista fixa de produtos
    { id: 1, nome: "Mouse", preco: 25.9 },
    { id: 2, nome: "Teclado", preco: 79.9 },
    { id: 3, nome: "Fone de Ouvido", preco: 49.9 },
    { id: 4, nome: "Caderno", preco: 18.5 },
    { id: 5, nome: "Monitor", preco: 899.0 },
    { id: 6, nome: "Camiseta", preco: 39.9 }
  ];

  let carrinho = carregarCarrinho(); // Carrega o carrinho salvo no localStorage
  let filtroAtual = "todos"; // Define o filtro inicial

  // guardar as constantes e o elemento da página que tem o id
  const listaProdutos = document.querySelector("#listaProdutos");
  const listaCarrinho = document.querySelector("#listaCarrinho");
  const valorTotal = document.querySelector("#valorTotal");
  const filtroProdutos = document.querySelector("#filtroProdutos");
  const btnLimparCarrinho = document.querySelector("#btnLimparCarrinho");

  function formatarMoeda(valor) { // Função para formatar número como dinheiro
    return valor.toLocaleString("pt-BR", { // Formata no padrão brasileiro
      style: "currency", // Estilo monetário
      currency: "BRL"
    });
  }

  function listarProdutos() { // Função que mostra os produtos na tela
    listaProdutos.innerHTML = ""; // Limpa a lista antes de recriar

    const produtosFiltrados = filtrarProdutos(produtos, filtroAtual); // Aplica o filtro atual

    produtosFiltrados.forEach((produto) => { // Percorre cada produto filtrado
      const card = document.createElement("div"); // Cria um bloco para o produto
      card.classList.add("card"); // Adiciona a classe CSS

      const nome = document.createElement("p"); // Cria parágrafo do nome
      nome.textContent = `Nome: ${produto.nome}`; // Mostra o nome do produto

      const preco = document.createElement("p"); // Cria parágrafo do preço
      preco.textContent = `Preço: ${formatarMoeda(produto.preco)}`; // Mostra o preço formatado

      const botao = document.createElement("button"); // Cria botão
      botao.textContent = "Adicionar ao Carrinho"; // Texto do botão
      botao.addEventListener("click", () => adicionarAoCarrinho(produto.id)); // Evento de clique

      // Adiciona ao card
      card.appendChild(nome);
      card.appendChild(preco);
      card.appendChild(botao);

      listaProdutos.appendChild(card); // Adiciona o card na lista
    });
  }

  function filtrarProdutos(lista, filtro) { // Função que filtra os produtos
    const resultado = []; // Cria um array vazio para armazenar o resultado

    lista.forEach((produto) => { // Percorre todos os produtos
      switch (filtro) { // Escolhe o tipo de filtro
        case "todos":
          resultado.push(produto); // Adiciona o produto no resultado
          break; // Sai do caso

        case "ate50":
          if (produto.preco <= 50) {
            resultado.push(produto);
          }
          break;

        case "acima50":
          if (produto.preco > 50) {
            resultado.push(produto);
          }
          break;
      }
    });

    return resultado; // Retorna a lista filtrada
  }

  function adicionarAoCarrinho(idProduto) {
    const produtoEncontrado = produtos.find((produto) => produto.id === idProduto); // Procura o produto na lista

    if (!produtoEncontrado) return; // Se não encontrar, encerra

    const itemExistente = carrinho.find((item) => item.id === idProduto); // Verifica se já existe no carrinho

    if (itemExistente) { // Se o item já estiver no carrinho
      itemExistente.quantidade += 1; // Aumenta a quantidade
    } else { // Se ainda não estiver no carrinho
      carrinho.push({ // Adiciona um novo item
        id: produtoEncontrado.id, // Guarda o id
        nome: produtoEncontrado.nome, // Guarda o nome
        preco: produtoEncontrado.preco, // Guarda o preço
        quantidade: 1 // Começa com quantidade 1
      });
    }

    salvarCarrinho(); // Salva no localStorage
    atualizarCarrinho(); // Atualiza a tela do carrinho
  }

  function removerDoCarrinho(idProduto) {
    const item = carrinho.find((produto) => produto.id === idProduto); // Procura o item no carrinho

    if (!item) return; // Se não encontrar, encerra

    if (item.quantidade > 1) { // Se houver mais de uma unidade
      item.quantidade -= 1; // Diminui a quantidade
    } else { // Se houver apenas uma unidade
      carrinho = carrinho.filter((produto) => produto.id !== idProduto); // Remove o item do carrinho
    }

    salvarCarrinho(); // Salva as mudanças
    atualizarCarrinho(); // Atualiza a tela
  }

  function atualizarCarrinho() {
    listaCarrinho.innerHTML = ""; // Limpa o carrinho antes de redesenhar

    if (carrinho.length === 0) { // Verifica se o carrinho está vazio
      const msg = document.createElement("p");
      msg.classList.add("mensagem-vazia");
      msg.textContent = "Carrinho vazio.";
      listaCarrinho.appendChild(msg); // Mostra na tela
      valorTotal.textContent = formatarMoeda(0); // Mostra total zerado
      return;
    }

    carrinho.forEach((item) => {
      const div = document.createElement("div"); // Cria uma caixa para o item
      div.classList.add("item-carrinho");

      const nome = document.createElement("p");
      nome.textContent = `Nome: ${item.nome}`;

      const quantidade = document.createElement("p");
      quantidade.textContent = `Quantidade: ${item.quantidade}`; // Exibe a quantidade

      const totalItem = document.createElement("p");
      totalItem.textContent = `Total do item: ${formatarMoeda(item.preco * item.quantidade)}`; // Calcula e mostra o total

      const botaoRemover = document.createElement("button");
      botaoRemover.textContent = "Remover";
      botaoRemover.addEventListener("click", () => removerDoCarrinho(item.id)); // Evento de clique

      // Adiciona ao item
      div.appendChild(nome);
      div.appendChild(quantidade);
      div.appendChild(totalItem);
      div.appendChild(botaoRemover);

      listaCarrinho.appendChild(div); // Adiciona o item na tela
    });

    atualizarTotal(); // Atualiza o valor total da compra
  }

  function atualizarTotal() {
    let total = 0;

    carrinho.forEach((item) => {
      total += item.preco * item.quantidade; // Soma preço vezes quantidade
    });

    valorTotal.textContent = formatarMoeda(total); // Mostra o total na tela
  }

  function salvarCarrinho() {
    localStorage.setItem("carrinho", JSON.stringify(carrinho)); // Transforma em texto e salva
  }

  function carregarCarrinho() {
    const dadosSalvos = localStorage.getItem("carrinho"); // Busca os dados salvos

    if (dadosSalvos) {
      return JSON.parse(dadosSalvos); // Converte de texto para objeto
    }

    return []; // Se não houver dados, retorna um carrinho vazio
  }

  function limparCarrinho() {
    carrinho = []; // Remove todos os itens da memória
    salvarCarrinho(); // Atualiza o localStorage
    atualizarCarrinho(); // Atualiza a tela
  }

  filtroProdutos.addEventListener("change", (event) => {
    filtroAtual = event.target.value; // Guarda o valor escolhido
    listarProdutos(); // Reexibe os produtos com o novo filtro
  });

  btnLimparCarrinho.addEventListener("click", limparCarrinho);
  
  listarProdutos(); // Mostra os produtos quando a página abre
  atualizarCarrinho(); // Mostra o carrinho quando a página abre
});